<?php

namespace Jabe\Delegate;

use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Value\TypedValueInterface;

interface VariableScopeInterface
{
    public function getVariableScopeKey(): ?string;

    public function getVariables(): \ArrayObject;

    public function getVariablesTyped(?bool $deserializeValues = true): VariableMapInterface;

    public function getVariablesLocal(): \ArrayObject;

    public function getVariablesLocalTyped(?bool $deserializeValues = false): VariableMapInterface;

    public function getVariable(?string $variableName);

    public function getVariableLocal(?string $variableName);

    public function getVariableTyped(?string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface;

    public function getVariableLocalTyped(?string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface;

    public function getVariableNames(): array;

    public function getVariableNamesLocal(): array;

    public function setVariable(?string $variableName, $value, ...$args): void;

    public function setVariableLocal(?string $variableName, $value, ...$args): void;

    public function setVariables(array $variables, ?bool $skipSerializationFormatCheck = null): void;

    public function setVariablesLocal($variables, ?bool $skipSerializationFormatCheck = null): void;

    public function hasVariables(): bool;

    public function hasVariablesLocal(): bool;

    public function hasVariable(?string $variableName): bool;

    public function hasVariableLocal(?string $variableName): bool;

    /**
     * Removes the variable and creates a new
     * HistoricVariableUpdateEntity.
     */
    public function removeVariable(?string $variableName): void;

    /**
     * Removes the local variable and creates a new
     * HistoricVariableUpdateEntity.
     */
    public function removeVariableLocal(?string $variableName): void;

    /**
     * Removes the variables and creates a new
     * HistoricVariableUpdateEntity for each of them.
     */
    public function removeVariables(?array $variableNames = []): void;

    /**
     * Removes the local variables and creates a new
     * HistoricVariableUpdateEntity for each of them.
     */
    public function removeVariablesLocal(?array $variableNames = []): void;
}
