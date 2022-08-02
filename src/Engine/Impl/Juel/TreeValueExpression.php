<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELContext,
    ELException,
    FunctionMapper,
    ValueExpression,
    ValueReference,
    VariableMapper
};

class TreeValueExpression extends ValueExpression
{
    private $builder; //serializable
    private $bindings; //serializable
    private $expr;
    private $type;
    private $deferred;

    private $node;

    private $structure;

    /**
     * Create a new value expression.
     * @param store used to get the parse tree from.
     * @param functions the function mapper used to bind functions
     * @param variables the variable mapper used to bind variables
     * @param expr the expression string
     * @param type the expected type (may be <code>null</code>)
     */
    public function __construct(TreeStore $store, ?FunctionMapper $functions, ?VariableMapper $variables, ?TypeConverter $converter, string $expr, ?string $type = null)
    {
        //parent::__construct();

        $tree = $store->get($expr);

        $this->builder = $store->getBuilder();
        $this->bindings = $tree->bind($functions, $variables, $converter);
        $this->expr = $expr;
        $this->type = $type;
        $this->node = $tree->getRoot();
        $this->deferred = $tree->isDeferred();
        if ($type === null) {
            throw new \Exception(LocalMessages::get("error.value.notype"));
        }
    }

    public function serialize()
    {
        return json_encode([
            'builder' => serialize($this->builder),
            'bindings' => serialize($this->bindings),
            'expr' => $this->expr,
            'type' => $this->type,
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

    public function getExpectedType(): string
    {
        return $this->type;
    }

    public function getExpressionString(): ?string
    {
        return $this->expr;
    }

  /**
   * Evaluates the expression as an lvalue and answers the result type.
   * @param context used to resolve properties (<code>base.property</code> and <code>base[property]</code>)
   * and to determine the result from the last base/property pair
   * @return lvalue evaluation type or <code>null</code> for rvalue expressions
   * @throws ELException if evaluation fails (e.g. property not found, type conversion failed, ...)
   */
    public function getType(ELContext $context): ?string
    {
        return $this->node->getType($this->bindings, $context);
    }

  /**
   * Evaluates the expression as an rvalue and answers the result.
   * @param context used to resolve properties (<code>base.property</code> and <code>base[property]</code>)
   * and to determine the result from the last base/property pair
   * @return rvalue evaluation result
   * @throws ELException if evaluation fails (e.g. property not found, type conversion failed, ...)
   */
    public function getValue(ELContext $context)
    {
        return $this->node->getValue($this->bindings, $context, $this->type);
    }

    /**
   * Evaluates the expression as an lvalue and determines if {@link #setValue(ELContext, Object)}
   * will always fail.
   * @param context used to resolve properties (<code>base.property</code> and <code>base[property]</code>)
   * and to determine the result from the last base/property pair
   * @return bool true if {@link #setValue(ELContext, Object)} always fails.
   * @throws ELException if evaluation fails (e.g. property not found, type conversion failed, ...)
     */
    public function isReadOnly(ELContext $context): bool
    {
        return $this->node->isReadOnly($this->bindings, $context);
    }

    /**
   * Evaluates the expression as an lvalue and assigns the given value.
   * @param context used to resolve properties (<code>base.property</code> and <code>base[property]</code>)
   * and to perform the assignment to the last base/property pair
   * @throws ELException if evaluation fails (e.g. property not found, type conversion failed, assignment failed...)
     */
    public function setValue(ELContext $context, $value): void
    {
        $this->node->setValue($this->bindings, $context, $value);
    }

    /**
     * @return bool true if this is a literal text expression
     */
    public function isLiteralText(): bool
    {
        return $this->node->isLiteralText();
    }

    public function getValueReference(ELContext $context): ?ValueReference
    {
        return $this->node->getValueReference($this->bindings, $context);
    }

    /**
     * Answer <code>true</code> if this could be used as an lvalue.
     * This is the case for eval expressions consisting of a simple identifier or
     * a nonliteral prefix, followed by a sequence of property operators (<code>.</code> or <code>[]</code>)
     */
    public function isLeftValue(): bool
    {
        return $this->node->isLeftValue();
    }

    /**
     * Answer <code>true</code> if this is a deferred expression (containing
     * sub-expressions starting with <code>#{</code>)
     */
    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    /**
     * Expressions are compared using the concept of a <em>structural id</em>:
   * variable and function names are anonymized such that two expressions with
   * same tree structure will also have the same structural id and vice versa.
     * Two value expressions are equal if
     * <ol>
     * <li>their structural id's are equal</li>
     * <li>their bindings are equal</li>
     * <li>their expected types are equal</li>
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
            return $this->getStructuralId() == $obj->getStructuralId() && $this->bindings->equals($obj->bindings);
        }
        return false;
    }

    public function __toString()
    {
        return "TreeValueExpression(" . $this->expr . ")";
    }

    /**
     * Print the parse tree.
     */
    public function dump(): string
    {
        return NodePrinter::dump($this->node);
    }
}
