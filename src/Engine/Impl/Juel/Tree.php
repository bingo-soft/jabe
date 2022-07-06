<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELException,
    FunctionMapper,
    ValueExpression,
    VariableMapper
};

class Tree
{
    private $root;
    private $functions = [];
    private $identifiers = [];
    private $deferred;

    /**
     *
     * Constructor.
     * @param root root node
     * @param functions collection of function nodes
     * @param identifiers collection of identifier nodes
     */
    public function __construct(ExpressionNode $root, array $functions, array $identifiers, bool $deferred)
    {
        $this->root = $root;
        $this->functions = $functions;
        $this->identifiers = $identifiers;
        $this->deferred = $deferred;
    }

    /**
     * Get function nodes (in no particular order)
     */
    public function getFunctionNodes(): array
    {
        return $this->functions;
    }

    /**
     * Get identifier nodes (in no particular order)
     */
    public function getIdentifierNodes(): array
    {
        return $this->identifiers;
    }

    /**
     * @return root node
     */
    public function getRoot(): ExpressionNode
    {
        return $this->root;
    }

    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    public function __toString()
    {
        return $this->getRoot()->getStructuralId(null);
    }

    /**
     * Create a bindings.
     * @param fnMapper the function mapper to use
     * @param varMapper the variable mapper to use
     * @param converter custom type converter
     * @return tree bindings
     */
    public function bind(?FunctionMapper $fnMapper, ?VariableMapper $varMapper, ?TypeConverter $converter = null): Bindings
    {
        $methods = [];
        if (!empty($this->functions)) {
            if ($fnMapper === null) {
                throw new ELException(LocalMessages::get("error.function.nomapper"));
            }
            foreach ($this->functions as $node) {
                $image = $node->getName();
                $method = null;
                $colon = strpos($image, ':');
                if ($colon === false) {
                    $method = $fnMapper->resolveFunction("", $image);
                } else {
                    $method = $fnMapper->resolveFunction(substr($image, 0, $colon), substr($image, $colon + 1));
                }
                if ($method === null) {
                    throw new ELException(LocalMessages::get("error.function.notfound", $image));
                }
                if ($node->isVarArgs() && $method->isVariadic()) {
                    if ($method->getNumberOfParameters() > $node->getParamCount() + 1) {
                        throw new ELException(LocalMessages::get("error.function.params", $image));
                    }
                } else {
                    if ($method->getNumberOfParameters() != $node->getParamCount()) {
                        throw new ELException(LocalMessages::get("error.function.params", $image));
                    }
                }
                $methods[$node->getIndex()] = $method;
            }
        }
        $expressions = [];
        if (count($this->identifiers) > 0) {
            foreach ($this->identifiers as $node) {
                $expression = null;
                if ($varMapper !== null) {
                    $expression = $varMapper->resolveVariable($node->getName());
                }
                $expressions[$node->getIndex()] = $expression;
            }
        }
        return new Bindings($methods, $expressions, $converter);
    }
}
