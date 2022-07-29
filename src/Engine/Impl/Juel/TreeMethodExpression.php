<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELContext,
    ELException,
    FunctionMapper,
    MethodExpression,
    MethodInfo,
    VariableMapper
};

class TreeMethodExpression extends MethodExpression
{
    private $builder;
    private $bindings;
    private $expr;
    private $type;
    private $types;
    private $deferred;

    private $node;

    private $structure;

    /**
     * Create a new method expression.
     * The expression must be an lvalue expression or literal text.
     * The expected return type may be <code>null</code>, meaning "don't care".
     * If it is an lvalue expression, the parameter types must not be <code>null</code>.
     * If it is literal text, the expected return type must not be <code>void</code>.
     * @param store used to get the parse tree from.
     * @param functions the function mapper used to bind functions
     * @param variables the variable mapper used to bind variables
     * @param expr the expression string
     * @param returnType the expected return type (may be <code>null</code>)
     * @param paramTypes the expected parameter types (must not be <code>null</code> for lvalues)
     */
    public function __construct(TreeStore $store, ?FunctionMapper $functions, ?VariableMapper $variables, ?TypeConverter $converter, string $expr, ?string $returnType = null, ?array $paramTypes = [])
    {
        $tree = $store->get($expr);

        $this->builder = $store->getBuilder();
        $this->bindings = $tree->bind($functions, $variables, $converter);
        $this->expr = $expr;
        $this->type = $returnType;
        $this->types = $paramTypes;
        $this->node = $tree->getRoot();
        $this->deferred = $tree->isDeferred();

        if ($this->node->isLiteralText()) {
            if ($returnType == "void") {
                throw new ELException(LocalMessages::get("error.method.literal.void", $expr));
            }
        } elseif (!$this->node->isMethodInvocation()) {
            if (!$this->node->isLeftValue()) {
                throw new ELException(LocalMessages::get("error.method.invalid", $expr));
            }
        }
    }

    public function serialize()
    {
        return json_encode([
            'builder' => serialize($this->builder),
            'bindings' => serialize($this->bindings),
            'expr' => $this->expr,
            'type' => $this->type,
            'types' => $this->types,
            'deferred' => $this->deferred
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->builder = unserialize($json->builder);
        $this->bindings = unserialize($json->bindings);
        $this->expr = $json->expr;
        $this->type = $json->type;
        $this->types = $json->types;
        $this->deferred = $json->deferred;
        $this->node = $this->builder->build($this->expr)->getRoot();
    }

    private function getStructuralId(): string
    {
        if ($this->structure === null) {
            $this->structure = $this->node->getStructuralId($this->bindings);
        }
        return $this->structure;
    }

    /**
    * Evaluates the expression and answers information about the method
    * @param context used to resolve properties (<code>base.property</code> and <code>base[property]</code>)
    * @return method information or <code>null</code> for literal expressions
    * @throws ELException if evaluation fails (e.g. suitable method not found)
    */
    public function getMethodInfo(ELContext $context): MethodInfo
    {
        return $this->node->getMethodInfo($this->bindings, $context, $this->type, $this->types);
    }

    public function getExpressionString(): ?string
    {
        return $this->expr;
    }

    /**
    * Evaluates the expression and invokes the method.
    * @param context used to resolve properties (<code>base.property</code> and <code>base[property]</code>)
    * @param paramValues
    * @return method result or <code>null</code> if this is a literal text expression
    * @throws ELException if evaluation fails (e.g. suitable method not found)
    */
    public function invoke(ELContext $context, ?array $paramValues = [])
    {
        return $this->node->invoke($this->bindings, $context, $this->type, $this->types, $paramValues);
    }

    /**
     * @return bool true if this is a literal text expression
     */
    public function isLiteralText(): bool
    {
        return $this->node->isLiteralText();
    }

    /**
     * @return bool true if this is a method invocation expression
     */
    public function isParmetersProvided(): bool
    {
        return $this->node->isMethodInvocation();
    }

    /**
     * Answer <code>true</code> if this is a deferred expression (starting with <code>#{</code>)
     */
    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    /**
     * Expressions are compared using the concept of a <em>structural id</em>:
     * variable and function names are anonymized such that two expressions with
     * same tree structure will also have the same structural id and vice versa.
     * Two method expressions are equal if
     * <ol>
     * <li>their builders are equal</li>
     * <li>their structural id's are equal</li>
     * <li>their bindings are equal</li>
     * <li>their expected types match</li>
     * <li>their parameter types are equal</li>
     * </ol>
     */
    public function equals($obj): bool
    {
        if ($obj !== null && get_class($obj) == get_class($this)) {
            if ($this->builder != $obj->builder) {
                return false;
            }
            if ($this->type != $obj->type) {
                return false;
            }
            if ($this->types != $obj->types) {
                return false;
            }
            return $this->getStructuralId() == $obj->getStructuralId() && $this->bindings->equals($obj->bindings);
        }
        return false;
    }

    public function __toString()
    {
        return "TreeMethodExpression(" . $this->expr . ")";
    }
}
