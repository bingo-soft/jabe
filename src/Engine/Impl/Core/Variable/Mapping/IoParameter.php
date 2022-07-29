<?php

namespace Jabe\Engine\Impl\Core\Variable\Mapping;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;

abstract class IoParameter
{
    /**
     * The name of the parameter. The name of the parameter is the
     * variable name in the target VariableScope.
     */
    protected $name;

    /**
     * The provider of the parameter value.
     */
    protected $valueProvider;

    public function __construct(string $name, ParameterValueProviderInterface $valueProvider)
    {
        $this->name = $name;
        $this->valueProvider = $valueProvider;
    }

    /**
     * Execute the parameter in a given variable scope.
     */
    //public void execute(AbstractVariableScope scope) {
    //    execute(scope, scope.getParentVariableScope());
    //}

    /**
     * @param innerScope
     * @param outerScope
     */
    abstract protected function execute(AbstractVariableScope $innerScope, ?AbstractVariableScope $outerScope = null): void;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValueProvider(): ParameterValueProviderInterface
    {
        return $this->valueProvider;
    }

    public function setValueProvider(ParameterValueProviderInterface $valueProvider): void
    {
        $this->valueProvider = $valueProvider;
    }
}
