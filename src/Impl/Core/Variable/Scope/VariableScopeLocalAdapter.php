<?php

namespace Jabe\Impl\Core\Variable\Scope;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Value\TypedValueInterface;

class VariableScopeLocalAdapter implements VariableScopeInterface
{
    protected $wrappedScope;

    public function __construct(VariableScopeInterface $wrappedScope)
    {
        $this->wrappedScope = $wrappedScope;
    }

    public function getVariableScopeKey(): ?string
    {
        return $this->wrappedScope->getVariableScopeKey();
    }

    public function getVariables(): array
    {
        return $this->getVariablesLocal();
    }

    public function getVariablesTyped(?bool $deserializeValues = true): VariableMapInterface
    {
        return $this->getVariablesLocalTyped($deserializeValues);
    }

    public function getVariablesLocal(): array
    {
        return $this->wrappedScope->getVariablesLocal();
    }

    public function getVariablesLocalTyped(?bool $deserializeValues = true): VariableMapInterface
    {
        return $this->wrappedScope->getVariablesLocalTyped($deserializeValues);
    }

    public function getVariable(?string $variableName)
    {
        return $this->getVariableLocal($variableName);
    }

    public function getVariableLocal(?string $variableName)
    {
        return $this->wrappedScope->getVariableLocal($variableName);
    }

    public function getVariableTyped(?string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface
    {
        return $this->getVariableLocalTyped($variableName, $deserializeValue);
    }

    public function getVariableLocalTyped(?string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface
    {
        return $this->wrappedScope->getVariableLocalTyped($variableName, $deserializeValue);
    }

    public function getVariableNames(): array
    {
        return $this->getVariableNamesLocal();
    }

    public function getVariableNamesLocal(): array
    {
        return $this->wrappedScope->getVariableNamesLocal();
    }

    public function setVariable(?string $variableName, $value): void
    {
        $this->setVariableLocal($variableName, $value);
    }

    public function setVariableLocal(?string $variableName, $value): void
    {
        $this->wrappedScope->setVariableLocal($variableName, $value);
    }

    public function setVariables(array $variables, ?bool $skipSerializationFormatCheck = null): void
    {
        $this->setVariablesLocal($variables);
    }

    public function setVariablesLocal(array $variables, ?bool $skipSerializationFormatCheck = null): void
    {
        $this->wrappedScope->setVariablesLocal($variables);
    }

    public function hasVariables(): bool
    {
        return $this->hasVariablesLocal();
    }

    public function hasVariablesLocal(): bool
    {
        return $this->wrappedScope->hasVariablesLocal();
    }

    public function hasVariable(?string $variableName): bool
    {
        return $this->hasVariableLocal($variableName);
    }

    public function hasVariableLocal(?string $variableName): bool
    {
        return $this->wrappedScope->hasVariableLocal($variableName);
    }

    public function removeVariable(?string $variableName): void
    {
        $this->removeVariableLocal($variableName);
    }

    public function removeVariableLocal(?string $variableName): void
    {
        $this->wrappedScope->removeVariableLocal($variableName);
    }

    public function removeVariables(?array $variableNames = []): void
    {
        $this->removeVariablesLocal($variableNames);
    }

    public function removeVariablesLocal(?array $variableNames = []): void
    {
        $this->wrappedScope->removeVariablesLocal($variableNames);
    }
}
