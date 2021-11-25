<?php

namespace BpmPlatform\Engine\Impl\Juel;

class Parser
{
    private const EXPR_FIRST = Symbol::IDENTIFIER . "|" . Symbol::STRING . "|" . Symbol::FLOAT . "|" .
                                 Symbol::INTEGER . "|" . Symbol::TRUE . "|" . Symbol::FALSE . "|" . Symbol::NULL . "|" .
                                 Symbol::MINUS . "|" . Symbol::NOT . "|" . Symbol::EMPTY . "|" . Symbol::LPAREN;

    protected $context;
    protected $scanner;

    private $identifiers = [];
    private $functions = [];
    private $lookahead = [];

    private $token; // current token
    private $position;// current token's position

    protected $extensions = [];

    public function __construct(Builder $context, string $input)
    {
        $this->context = $context;
        $this->scanner = $this->createScanner($input);
    }

    protected function createScanner(string $expression): Scanner
    {
        return new Scanner($expression);
    }

    public function putExtensionHandler(ExtensionToken $token, ExtensionHandler $extension): void
    {
        $this->extensions[] = [$token, $extension];
    }

    protected function getExtensionHandler(Token $token): ?ExtensionHandler
    {
        $extension = null;
        foreach ($this->extensions as $ext) {
            if ($ext[0] == $token) {
                return $ext[1];
            }
        }
        return null;
    }

    /**
     * Parse an integer literal.
     * @param string string to parse
     * @return <code>Long.valueOf(string)</code>
     */
    protected function parseInteger(string $string): ?int
    {
        try {
            return intval($string);
        } catch (\Exception $e) {
            $this->fail(Symbol::INTEGER);
            return null;
        }
    }

    /**
     * Parse a floating point literal.
     * @param string string to parse
     * @return <code>Double.valueOf(string)</code>
     */
    protected function parseFloat(string $string): ?float
    {
        try {
            return floatval($string);
        } catch (\Exception $e) {
            $this->fail(Symbol::FLOAT);
            return null;
        }
    }

    protected function createAstBinary(AstNode $left, AstNode $right, BinaryOperator $operator): AstBinary
    {
        return new AstBinary($left, $right, $operator);
    }

    protected function createAstBracket(AstNode $base, AstNode $property, bool $lvalue, bool $strict): AstBracket
    {
        return new AstBracket($base, $property, $lvalue, $strict);
    }

    protected function createAstChoice(AstNode $question, AstNode $yes, AstNode $no): AstChoice
    {
        return new AstChoice($question, $yes, $no);
    }

    protected function createAstComposite(array $nodes): AstComposite
    {
        return new AstComposite($nodes);
    }

    protected function createAstDot(AstNode $base, string $property, bool $lvalue): AstDot
    {
        return new AstDot($base, $property, $lvalue);
    }

    protected function createAstFunction(string $name, int $index, AstParameters $params): AstFunction
    {
        return new AstFunction($name, $index, $params, $this->context->isEnabled(Feature::VARARGS));
    }

    protected function createAstIdentifier(string $name, int $index): AstIdentifier
    {
        return new AstIdentifier($name, $index);
    }

    protected function createAstMethod(AstProperty $property, AstParameters $params): AstMethod
    {
        return new AstMethod($property, $params);
    }

    protected function createAstUnary(AstNode $child, UnaryOperator $operator): AstUnary
    {
        return new AstUnary($child, $operator);
    }

    protected function getFunctions(): array
    {
        return $this->functions;
    }

    protected function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    protected function getToken(): Token
    {
        return $this->token;
    }

    /**
     * throw exception
     */
    protected function fail(string $expected): void
    {
        throw new ParseException($this->position, "'" . $this->token->getImage() . "'", $expected);
    }

    /**
     * get lookahead symbol.
     */
    protected function lookahead(int $index): Token
    {
        while ($index >= count($this->lookahead)) {
            $this->lookahead[] = new LookaheadToken($this->scanner->next(), $this->scanner->getPosition());
        }
        return $this->lookahead[$index]->token;
    }

    /**
     * consume current token ($this->get next token).
     * @return the consumed token (which was the current token when calling this method)
     */
    protected function consumeToken(?string $expected = null): ?Token
    {
        if ($expected != null) {
            if ($this->token->getSymbol() != $expected) {
                $this->fail($expected);
            }
            return $this->consumeToken();
        }
        $result = $this->token;
        if (empty($this->lookahead)) {
            $this->token = $this->scanner->next();
            $this->position = $this->scanner->getPosition();
        } else {
            $next = $this->lookahead[0];
            unset($this->lookahead[0]);
            $this->token = $next->token;
            $this->position = $next->position;
        }
        return $result;
    }

    /**
     * tree := text? ((dynamic text?)+ | (deferred text?)+)?
     */
    public function tree(): Tree
    {
        $this->consumeToken();
        $t = $this->text();
        if ($this->token->getSymbol() == Symbol::EOF) {
            if ($t === null) {
                $t = new AstText("");
            }
            return new Tree($t, $this->functions, $this->identifiers, false);
        }
        $e = $this->eval();
        if ($this->token->getSymbol() == Symbol::EOF && $t === null) {
            return new Tree($e, $this->functions, $this->identifiers, $e->isDeferred());
        }
        $list = [];
        if ($t !== null) {
            $list[] = $t;
        }
        $list[] = $e;
        $t = $this->text();
        if ($t !== null) {
            $list[] = $t;
        }
        while ($this->token->getSymbol() != Symbol::EOF) {
            if ($e->isDeferred()) {
                $list[] = $this->eval(true, true);
            } else {
                $list[] = $this->eval(true, false);
            }
            $t = $this->text();
            if ($t !== null) {
                $list[] = $t;
            }
        }
        return new Tree($this->createAstComposite($list), $this->functions, $this->identifiers, $e->isDeferred());
    }

    /**
     * text := &lt;TEXT&gt;
     */
    protected function text(): ?AstNode
    {
        $v = null;
        if ($this->token->getSymbol() == Symbol::TEXT) {
            $v = new AstText($this->token->getImage());
            $this->consumeToken();
        }
        return $v;
    }

    /**
     * dynmamic := &lt;START_EVAL_DYNAMIC&gt; expr &lt;END_EVAL&gt;
     * deferred := &lt;START_EVAL_DEFERRED&gt; expr &lt;END_EVAL&gt;
     */
    protected function eval(?bool $required = null, ?bool $deferred = null): ?AstEval
    {
        if ($required === null && $deferred === null) {
            $e = $this->eval(false, false);
            if ($e === null) {
                $e = $this->eval(false, true);
                if ($e === null) {
                    $this->fail(Symbol::START_EVAL_DEFERRED . "|" . Symbol::START_EVAL_DYNAMIC);
                }
            }
            return $e;
        } else {
            $v = null;
            $startEval = $deferred ? Symbol::START_EVAL_DEFERRED : Symbol::START_EVAL_DYNAMIC;
            if ($this->token->getSymbol() == $startEval) {
                $this->consumeToken();
                $v = new AstEval($this->expr(true), $deferred);
                $this->consumeToken(Symbol::END_EVAL);
            } elseif ($required) {
                $this->fail($startEval);
            }
            return $v;
        }
    }

    /**
     * expr := or (&lt;QUESTION&gt; expr &lt;COLON&gt; expr)?
     */
    protected function expr(bool $required): ?AstNode
    {
        $v = $this->or($required);
        if ($v === null) {
            return null;
        }
        if ($this->token->getSymbol() == Symbol::QUESTION) {
            $this->consumeToken();
            $a = $this->expr(true);
            $this->consumeToken(Symbol::COLON);
            $b = $this->expr(true);
            $v = $this->createAstChoice($v, $a, $b);
        }
        return $v;
    }

    /**
     * or := and (&lt;OR&gt; and)*
     */
    protected function or(bool $required): ?AstNode
    {
        $v = $this->and($required);
        if ($v === null) {
            return null;
        }
        while (true) {
            switch ($this->token->getSymbol()) {
                case Symbol::OR:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->and(true), AstBinary::or());
                    break;
                case Symbol::EXTENSION:
                    if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::OR) {
                        $v = $this->getExtensionHandler($this->consumeToken())->createAstNode($v, $this->and(true));
                        break;
                    }
                    //
                default:
                    return $v;
            }
        }
    }

    /**
     * and := eq (&lt;AND&gt; eq)*
     */
    protected function and(bool $required): ?AstNode
    {
        $v = $this->eq($required);
        if ($v === null) {
            return null;
        }
        while (true) {
            switch ($this->token->getSymbol()) {
                case Symbol::AND:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->eq(true), AstBinary::and());
                    break;
                case Symbol::EXTENSION:
                    if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::AND) {
                        $v = $this->getExtensionHandler($this->consumeToken())->createAstNode($v, $this->eq(true));
                        break;
                    }
                    //
                default:
                    return $v;
            }
        }
    }

    /**
     * eq := cmp (&lt;EQ&gt; cmp | &lt;NE&gt; cmp)*
     */
    protected function eq(bool $required): ?AstNode
    {
        $v = $this->cmp($required);
        if ($v === null) {
            return null;
        }
        while (true) {
            switch ($this->token->getSymbol()) {
                case Symbol::EQ:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->cmp(true), AstBinary::eq());
                    break;
                case Symbol::NE:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->cmp(true), AstBinary::ne());
                    break;
                case Symbol::EXTENSION:
                    if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::eq()) {
                        $v = $this->getExtensionHandler($this->consumeToken())->createAstNode($v, $this->cmp(true));
                        break;
                    }
                    //
                default:
                    return $v;
            }
        }
    }

    /**
     * cmp := add (&lt;LT&gt; add | &lt;LE&gt; add | &lt;GE&gt; add | &lt;GT&gt; add)*
     */
    protected function cmp(bool $required): ?AstNode
    {
        $v = $this->add($required);
        if ($v === null) {
            return null;
        }
        while (true) {
            switch ($this->token->getSymbol()) {
                case Symbol::LT:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->add(true), AstBinary::lt());
                    break;
                case Symbol::LE:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->add(true), AstBinary::le());
                    break;
                case Symbol::GE:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->add(true), AstBinary::ge());
                    break;
                case Symbol::GT:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->add(true), AstBinary::gt());
                    break;
                case Symbol::EXTENSION:
                    if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::CMP) {
                        $v = $this->getExtensionHandler($this->consumeToken())->createAstNode($v, $this->add(true));
                        break;
                    }
                    //
                default:
                    return $v;
            }
        }
    }

    /**
     * add := add (&lt;PLUS&gt; mul | &lt;MINUS&gt; mul)*
     */
    protected function add(bool $required): ?AstNode
    {
        $v = $this->mul($required);
        if ($v === null) {
            return null;
        }
        while (true) {
            switch ($this->token->getSymbol()) {
                case Symbol::PLUS:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->mul(true), AstBinary::add());
                    break;
                case Symbol::MINUS:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->mul(true), AstBinary::sub());
                    break;
                case Symbol::EXTENSION:
                    if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::ADD) {
                        $v = $this->getExtensionHandler($this->consumeToken())->createAstNode($v, $this->mul(true));
                        break;
                    }
                    //
                default:
                    return $v;
            }
        }
    }

    /**
     * mul := unary (&lt;MUL&gt; unary | &lt;DIV&gt; unary | &lt;MOD&gt; unary)*
     */
    protected function mul(bool $required): ?AstNode
    {
        $v = $this->unary($required);
        if ($v === null) {
            return null;
        }
        while (true) {
            switch ($this->token->getSymbol()) {
                case Symbol::MUL:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->unary(true), AstBinary::mul());
                    break;
                case Symbol::DIV:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->unary(true), AstBinary::div());
                    break;
                case Symbol::MOD:
                    $this->consumeToken();
                    $v = $this->createAstBinary($v, $this->unary(true), AstBinary::mod());
                    break;
                case Symbol::EXTENSION:
                    if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::MUL) {
                        $v = $this->getExtensionHandler($this->consumeToken())->createAstNode($v, $this->unary(true));
                        break;
                    }
                    //
                default:
                    return $v;
            }
        }
    }

    /**
     * unary := &lt;NOT&gt; unary | &lt;MINUS&gt; unary | &lt;EMPTY&gt; unary | value
     */
    protected function unary(bool $required): ?AstNode
    {
        $v = null;
        switch ($this->token->getSymbol()) {
            case Symbol::NOT:
                $this->consumeToken();
                $v = $this->createAstUnary($this->unary(true), AstUnary::not());
                break;
            case Symbol::MINUS:
                $this->consumeToken();
                $v = $this->createAstUnary($this->unary(true), AstUnary::neg());
                break;
            case Symbol::EMPTY:
                $this->consumeToken();
                $v = $this->createAstUnary($this->unary(true), AstUnary::empty());
                break;
            case Symbol::EXTENSION:
                if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::UNARY) {
                    $v = $this->getExtensionHandler($this->consumeToken())->createAstNode($this->unary(true));
                    break;
                }
                //
            default:
                $v = $this->value();
        }
        if ($v === null && $required) {
            var_dump($this->token);
            $this->fail(self::EXPR_FIRST);
        }
        return $v;
    }

    /**
     * value := (nonliteral | literal) (&lt;DOT&gt; &lt;IDENTIFIER&gt; | &lt;LBRACK&gt; expr &lt;RBRACK&gt;)*
     */
    protected function value(): ?AstNode
    {
        $lvalue = true;
        $v = $this->nonliteral();
        if ($v === null) {
            $v = $this->literal();
            if ($v === null) {
                return null;
            }
            $lvalue = false;
        }
        while (true) {
            switch ($this->token->getSymbol()) {
                case Symbol::DOT:
                    $this->consumeToken();
                    $name = $this->consumeToken(Symbol::IDENTIFIER)->getImage();
                    $dot = $this->createAstDot($v, $name, $lvalue);
                    if ($this->token->getSymbol() == Symbol::LPAREN && $this->context->isEnabled(Feature::METHOD_INVOCATIONS)) {
                        $v = $this->createAstMethod($dot, $this->params());
                    } else {
                        $v = $dot;
                    }
                    break;
                case Symbol::LBRACK:
                    $this->consumeToken();
                    $property = $this->expr(true);
                    $strict = !$this->context->isEnabled(Feature::NULL_PROPERTIES);
                    $this->consumeToken(Symbol::RBRACK);
                    $bracket = $this->createAstBracket($v, $property, $lvalue, $strict);
                    if ($this->token->getSymbol() == Symbol::LPAREN && $this->context->isEnabled(Feature::METHOD_INVOCATIONS)) {
                        $v = $this->createAstMethod($bracket, $this->params());
                    } else {
                        $v = $bracket;
                    }
                    break;
                default:
                    return $v;
            }
        }
    }

    /**
     * nonliteral := &lt;IDENTIFIER&gt; | function | &lt;LPAREN&gt; expr &lt;RPAREN&gt;
     * function   := (&lt;IDENTIFIER&gt; &lt;COLON&gt;)? &lt;IDENTIFIER&gt; &lt;LPAREN&gt; list? &lt;RPAREN&gt;
     */
    protected function nonliteral(): ?AstNode
    {
        $v = null;
        switch ($this->token->getSymbol()) {
            case Symbol::IDENTIFIER:
                $name = $this->consumeToken()->getImage();
                if ($this->token->getSymbol() == Symbol::COLON && $this->lookahead(0)->getSymbol() == Symbol::IDENTIFIER && $this->lookahead(1)->getSymbol() == Symbol::LPAREN) { // ns:f(...)
                    $this->consumeToken();
                    $name .= ":" . $this->token->getImage();
                    $this->consumeToken();
                }
                if ($this->token->getSymbol() == Symbol::LPAREN) { // function
                    $v = $this->function0($name, $this->params());
                } else { // identifier
                    $v = $this->identifier($name);
                }
                break;
            case Symbol::LPAREN:
                $this->consumeToken();
                $v = $this->expr(true);
                $this->consumeToken(Symbol::RPAREN);
                $v = new AstNested($v);
                break;
        }
        return $v;
    }

    /**
     * params := &lt;LPAREN&gt; (expr (&lt;COMMA&gt; expr)*)? &lt;RPAREN&gt;
     */
    protected function params(): AstParameters
    {
        $this->consumeToken(Symbol::LPAREN);
        $l = [];
        $v = $this->expr(false);
        if ($v != null) {
            $l[] = $v;
            while ($this->token->getSymbol() == Symbol::COMMA) {
                $this->consumeToken();
                $l[] = $this->expr(true);
            }
        }
        $this->consumeToken(Symbol::RPAREN);
        return new AstParameters($l);
    }

    /**
     * literal := &lt;TRUE&gt; | &lt;FALSE&gt; | &lt;STRING&gt; | &lt;INTEGER&gt; | &lt;FLOAT&gt; | &lt;NULL&gt;
     */
    protected function literal(): ?AstNode
    {
        $v = null;
        switch ($this->token->getSymbol()) {
            case Symbol::TRUE:
                $v = new AstBoolean(true);
                $this->consumeToken();
                break;
            case Symbol::FALSE:
                $v = new AstBoolean(false);
                $this->consumeToken();
                break;
            case Symbol::STRING:
                $v = new AstString($this->token->getImage());
                $this->consumeToken();
                break;
            case Symbol::INTEGER:
                $v = new AstNumber($this->parseInteger($this->token->getImage()));
                $this->consumeToken();
                break;
            case Symbol::FLOAT:
                $v = new AstNumber($this->parseFloat($this->token->getImage()));
                $this->consumeToken();
                break;
            case Symbol::NULL:
                $v = new AstNull();
                $this->consumeToken();
                break;
            case Symbol::EXTENSION:
                if ($this->getExtensionHandler($this->token)->getExtensionPoint() == ExtensionPoint::LITERAL) {
                    $v = $this->getExtensionHandler($this->consumeToken())->createAstNode();
                    break;
                }
        }
        return $v;
    }

    protected function function0(string $name, AstParameters $params): AstFunction
    {
        $function = $this->createAstFunction($name, count($this->functions), $params);
        $this->functions[] = $function;
        return $function;
    }

    protected function identifier(string $name): ?AstIdentifier
    {
        $identifier = $this->createAstIdentifier($name, count($this->identifiers));
        $this->identifiers[] = $identifier;
        return $identifier;
    }
}
