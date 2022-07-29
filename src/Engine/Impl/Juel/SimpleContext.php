<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELContext,
    ELResolver,
    FunctionMapper,
    ValueExpression,
    VariableMapper
};

class SimpleContext extends ELContext
{
    private $functions;
    private $variables;
    private $resolver;

    /**
     * Create a context, use the specified resolver.
     */
    public function __construct(?ELResolver $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * Define a function.
     */
    public function setFunction(string $prefix, string $localName, \ReflectionMethod $method): void
    {
        if ($this->functions === null) {
            $this->functions = new Functions();
        }
        $this->functions->setFunction($prefix, $localName, $method);
    }

    /**
     * Define a variable.
     */
    public function setVariable(string $name, ValueExpression $expression): ValueExpression
    {
        if ($this->variables === null) {
            $this->variables = new Variables();
        }
        return $this->variables->setVariable($name, $expression);
    }

    /**
     * Get our function mapper.
     */
    public function getFunctionMapper(): ?FunctionMapper
    {
        if ($this->functions === null) {
            $this->functions = new Functions();
        }
        return $this->functions;
    }

    /**
     * Get our variable mapper.
     */
    public function getVariableMapper(): ?VariableMapper
    {
        if ($this->variables === null) {
            $this->variables = new Variables();
        }
        return $this->variables;
    }

    /**
     * Get our resolver. Lazy initialize to a SimpleResolver if necessary.
     */
    public function getELResolver(): ?ELResolver
    {
        if ($this->resolver === null) {
            $this->resolver = new SimpleResolver();
        }
        return $this->resolver;
    }

    /**
     * Set our resolver.
     *
     * @param resolver
     */
    public function setELResolver(ELResolver $resolver): void
    {
        $this->resolver = $resolver;
    }
}
