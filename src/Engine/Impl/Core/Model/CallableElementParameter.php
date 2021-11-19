<?php

namespace BpmPlatform\Engine\Impl\Core\Model;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\Value\{
    ConstantValueProvider,
    ParameterValueProviderInterface
};
use BpmPlatform\Engine\Impl\Core\Variable\Scope\VariableScopeLocalAdapter;
use BpmPlatform\Engine\Variable\VariableMapInterface;

class CallableElementParameter
{
    protected $sourceValueProvider;
    protected $target;
    protected $allVariables = false;
    protected $readLocal = false;

    public function getSource(VariableScopeInterface $variableScope)
    {
        if ($this->sourceValueProvider instanceof ConstantValueProvider) {
            $variableName = $this->sourceValueProvider->getValue($variableScope);

            return $variableScope->getVariableTyped($variableName);
        } else {
            return $this->sourceValueProvider->getValue($variableScope);
        }
    }

    public function applyTo(VariableScopeInterface $variableScope, VariableMapInterface $variables): void
    {
        if ($this->readLocal) {
            $variableScope = new VariableScopeLocalAdapter($variableScope);
        }

        if ($this->allVariables) {
            $allVariables = $variableScope->getVariables();
            $variables->putAll($allVariables);
        } else {
            $value = $this->getSource($variableScope);
            $variables->put($target, $value);
        }
    }

    public function getSourceValueProvider(): ParameterValueProviderInterface
    {
        return $this->sourceValueProvider;
    }

    public function setSourceValueProvider(ParameterValueProviderInterface $source): void
    {
        $this->sourceValueProvider = $source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function isAllVariables(): bool
    {
        return $this->allVariables;
    }

    public function setAllVariables(bool $allVariables): void
    {
        $this->allVariables = $allVariables;
    }

    public function setReadLocal(bool $readLocal): void
    {
        $this->readLocal = $readLocal;
    }

    public function isReadLocal(): bool
    {
        return $this->readLocal;
    }
}
