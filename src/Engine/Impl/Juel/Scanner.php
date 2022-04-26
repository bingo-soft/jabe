<?php

namespace Jabe\Engine\Impl\Juel;

class Scanner
{
    private static $KEYMAP = [];
    private static $FIXMAP = [];

    private $token;  // current token
    private $position = 0; // start position of current token
    private $input;
    protected $builder = "";

    private static function addFixToken(Token $token): void
    {
        self::$FIXMAP[$token->getSymbol()] = $token;
    }

    private static function addKeyToken(Token $token): void
    {
        self::$KEYMAP[$token->getImage()] = $token;
    }

    public function __construct(string $input)
    {
        $this->input = $input;
        if (empty(self::$FIXMAP)) {
            self::addFixToken(new Token(Symbol::PLUS, "+"));
            self::addFixToken(new Token(Symbol::MINUS, "-"));
            self::addFixToken(new Token(Symbol::MUL, "*"));
            self::addFixToken(new Token(Symbol::DIV, "/"));
            self::addFixToken(new Token(Symbol::MOD, "%"));
            self::addFixToken(new Token(Symbol::LPAREN, "("));
            self::addFixToken(new Token(Symbol::RPAREN, ")"));
            self::addFixToken(new Token(Symbol::NOT, "!"));
            self::addFixToken(new Token(Symbol::AND, "&&"));
            self::addFixToken(new Token(Symbol::OR, "||"));
            self::addFixToken(new Token(Symbol::EQ, "=="));
            self::addFixToken(new Token(Symbol::NE, "!="));
            self::addFixToken(new Token(Symbol::LT, "<"));
            self::addFixToken(new Token(Symbol::LE, "<="));
            self::addFixToken(new Token(Symbol::GT, ">"));
            self::addFixToken(new Token(Symbol::GE, ">="));
            self::addFixToken(new Token(Symbol::QUESTION, "?"));
            self::addFixToken(new Token(Symbol::COLON, ":"));
            self::addFixToken(new Token(Symbol::COMMA, ","));
            self::addFixToken(new Token(Symbol::DOT, "."));
            self::addFixToken(new Token(Symbol::LBRACK, "["));
            self::addFixToken(new Token(Symbol::RBRACK, "]"));
            self::addFixToken(new Token(Symbol::START_EVAL_DEFERRED, "#{"));
            self::addFixToken(new Token(Symbol::START_EVAL_DYNAMIC, '${'));
            self::addFixToken(new Token(Symbol::END_EVAL, "}"));
            self::addFixToken(new Token(Symbol::EOF, null, 0));

            self::addKeyToken(new Token(Symbol::NULL, "null"));
            self::addKeyToken(new Token(Symbol::TRUE, "true"));
            self::addKeyToken(new Token(Symbol::FALSE, "false"));
            self::addKeyToken(new Token(Symbol::EMPTY, "empty"));
            self::addKeyToken(new Token(Symbol::DIV, "div"));
            self::addKeyToken(new Token(Symbol::MOD, "mod"));
            self::addKeyToken(new Token(Symbol::NOT, "not"));
            self::addKeyToken(new Token(Symbol::AND, "and"));
            self::addKeyToken(new Token(Symbol::OR, "or"));
            self::addKeyToken(new Token(Symbol::LE, "le"));
            self::addKeyToken(new Token(Symbol::LT, "lt"));
            self::addKeyToken(new Token(Symbol::EQ, "eq"));
            self::addKeyToken(new Token(Symbol::NE, "ne"));
            self::addKeyToken(new Token(Symbol::GE, "ge"));
            self::addKeyToken(new Token(Symbol::GT, "gt"));
            self::addKeyToken(new Token(Symbol::INSTANCEOF, "instanceof"));
        }
    }

    public function getInput(): string
    {
        return $this->input;
    }

    /**
     * @return current token
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @return current input position
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return <code>true</code> iff the specified character is a digit
     */
    protected function isDigit(string $c): bool
    {
        return strlen($c) == 1 && is_numeric($c);
    }

    /**
     * @param s name
     * @return token for the given keyword or <code>null</code>
     */
    protected function keyword(string $s): ?Token
    {
        if (array_key_exists($s, self::$KEYMAP)) {
            return self::$KEYMAP[$s];
        }
        return null;
    }

    /**
     * @param symbol
     * @return token for the given symbol
     */
    protected function fixed(string $symbol): ?Token
    {
        if (array_key_exists($symbol, self::$FIXMAP)) {
            return self::$FIXMAP[$symbol];
        }
        return null;
    }

    protected function token(string $symbol, string $value, int $length): Token
    {
        return new Token($symbol, $value, $length);
    }

    protected function isEval(): bool
    {
        return $this->token != null && $this->token->getSymbol() != Symbol::TEXT && $this->token->getSymbol() != Symbol::END_EVAL;
    }

    /**
     * text token
     */
    protected function nextText(): Token
    {
        $this->builder = "";
        $i = $this->position;
        $l = strlen($this->input);
        $escaped = false;
        while ($i < $l) {
            $c = $this->input[$i];
            switch ($c) {
                case '\\':
                    if ($escaped) {
                        $this->builder .= '\\';
                    } else {
                        $escaped = true;
                    }
                    break;
                case '#':
                case '$':
                    if ($i + 1 < $l && $this->input[$i + 1] == '{') {
                        if ($escaped) {
                            $this->builder .= $c;
                        } else {
                            return token(Symbol::TEXT, $this->builder, $i - $this->position);
                        }
                    } else {
                        if ($escaped) {
                            $this->builder .= '\\';
                        }
                        $this->builder .= $c;
                    }
                    $escaped = false;
                    break;
                default:
                    if ($escaped) {
                        $this->builder .= '\\';
                    }
                    $this->builder .= $c;
                    $escaped = false;
            }
            $i += 1;
        }
        if ($escaped) {
            $this->builder .= '\\';
        }
        return $this->token(Symbol::TEXT, $this->builder, $i - $this->position);
    }

    /**
     * string token
     */
    protected function nextString(): Token
    {
        $this->builder = "";
        $quote = $this->input[$this->position];
        $i = $this->position + 1;
        $l = strlen($this->input);
        while ($i < $l) {
            $c = $this->input[$i++];
            if ($c == '\\') {
                if ($i == l) {
                    throw new ScanException($this->position, "unterminated string", $quote . " or \\");
                } else {
                    $c = $this->input[$i++];
                    if ($c == '\\' || $c == $quote) {
                        $this->builder .= $c;
                    } else {
                        throw new ScanException($this->position, "invalid escape sequence \\" . $c, "\\" . $quote . " or \\\\");
                    }
                }
            } elseif ($c == $quote) {
                return $this->token(Symbol::STRING, $this->builder, $i - $this->position);
            } else {
                $this->builder .= $c;
            }
        }
        throw new ScanException($this->position, "unterminated string", $quote);
    }

    /**
     * number token
     */
    protected function nextNumber(): Token
    {
        $i = $this->position;
        $l = strlen($this->input);
        while ($i < $l && $this->isDigit($this->input[$i])) {
            $i++;
        }
        $symbol = Symbol::INTEGER;
        if ($i < $l && $this->input[$i] == '.') {
            $i++;
            while ($i < $l && $this->isDigit($this->input[$i])) {
                $i++;
            }
            $symbol = Symbol::FLOAT;
        }
        if ($i < $l && ($this->input[$i] == 'e' || $this->input[$i] == 'E')) {
            $e = $i;
            $i++;
            if ($i < $l && ($this->input[$i] == '+' || $this->input[$i] == '-')) {
                $i++;
            }
            if ($i < $l && $this->isDigit($this->input[$i])) {
                $i++;
                while ($i < $l && $this->isDigit($this->input[$i])) {
                    $i++;
                }
                $symbol = Symbol::FLOAT;
            } else {
                $i = $e;
            }
        }
        return $this->token($symbol, substr($this->input, $this->position, $i - $this->position), $i - $this->position);
    }

    /**
     * token inside an eval expression
     */
    protected function nextEval(): Token
    {
        $c1 = $this->input[$this->position];
        $c2 = $this->position < strlen($this->input) - 1 ? $this->input[$this->position + 1] : '0';

        switch ($c1) {
            case '*':
                return $this->fixed(Symbol::MUL);
            case '/':
                return $this->fixed(Symbol::DIV);
            case '%':
                return $this->fixed(Symbol::MOD);
            case '+':
                return $this->fixed(Symbol::PLUS);
            case '-':
                return $this->fixed(Symbol::MINUS);
            case '?':
                return $this->fixed(Symbol::QUESTION);
            case ':':
                return $this->fixed(Symbol::COLON);
            case '[':
                return $this->fixed(Symbol::LBRACK);
            case ']':
                return $this->fixed(Symbol::RBRACK);
            case '(':
                return $this->fixed(Symbol::LPAREN);
            case ')':
                return $this->fixed(Symbol::RPAREN);
            case ',':
                return $this->fixed(Symbol::COMMA);
            case '.':
                if (!$this->isDigit($c2)) {
                    return $this->fixed(Symbol::DOT);
                }
                break;
            case '=':
                if ($c2 == '=') {
                    return $this->fixed(Symbol::EQ);
                }
                break;
            case '&':
                if ($c2 == '&') {
                    return $this->fixed(Symbol::AND);
                }
                break;
            case '|':
                if ($c2 == '|') {
                    return $this->fixed(Symbol::OR);
                }
                break;
            case '!':
                if ($c2 == '=') {
                    return $this->fixed(Symbol::NE);
                }
                return $this->fixed(Symbol::NOT);
            case '<':
                if ($c2 == '=') {
                    return $this->fixed(Symbol::LE);
                }
                return $this->fixed(Symbol::LT);
            case '>':
                if ($c2 == '=') {
                    return $this->fixed(Symbol::GE);
                }
                return $this->fixed(Symbol::GT);
            case '"':
            case '\'':
                return $this->nextString();
        }

        if ($this->isDigit($c1) || $c1 == '.') {
            return $this->nextNumber();
        }

        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $c1)) {
            $i = $this->position + 1;
            $l = strlen($this->input);
            while ($i < $l && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $this->input[$i])) {
                $i++;
            }
            $name = substr($this->input, $this->position, $i - $this->position);
            $keyword = $this->keyword($name);
            return $keyword == null ? $this->token(Symbol::IDENTIFIER, $name, $i - $this->position) : $keyword;
        }

        throw new ScanException($this->position, "invalid character '" . $c1 . "'", "expression token");
    }

    protected function nextToken(): Token
    {
        if ($this->isEval()) {
            if ($this->input[$this->position] == '}') {
                return $this->fixed(Symbol::END_EVAL);
            }
            return $this->nextEval();
        } else {
            if ($this->position + 1 < strlen($this->input) && $this->input[$this->position + 1] == '{') {
                switch ($this->input[$this->position]) {
                    case '#':
                        return $this->fixed(Symbol::START_EVAL_DEFERRED);
                    case '$':
                        return $this->fixed(Symbol::START_EVAL_DYNAMIC);
                }
            }
            return $this->nextText();
        }
    }

    /**
     * Scan next token.
     * After calling this method, {@link #getToken()} and {@link #getPosition()}
     * can be used to retreive the token's image and input position.
     * @return scanned token
     */
    public function next(): Token
    {
        if ($this->token !== null) {
            $this->position += $this->token->getSize();
        }

        $length = strlen($this->input);

        if ($this->isEval()) {
            while ($this->position < $length && ctype_space($this->input[$this->position])) {
                $this->position++;
            }
        }

        if ($this->position == $length) {
            $this->token = $this->fixed(Symbol::EOF);
            return $this->token;
        }

        $this->token = $this->nextToken();
        return $this->token;
    }
}
