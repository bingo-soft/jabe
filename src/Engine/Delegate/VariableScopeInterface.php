<?php

namespace BpmPlatform\Engine\Delegate;

use BpmPlatform\Engine\Variable\VariableMapInterface;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

interface VariableScopeInterface
{
    public function getVariableScopeKey(): string;

    public function getVariables(): array;

    public function getVariablesTyped(?bool $deserializeValues = false): VariableMapInterface;

    public function getVariablesLocal(): array;

    public function getVariablesLocalTyped(?bool $deserializeValues = false): VariableMapInterface;

    public function getVariable(string $variableName);

    public function getVariableLocal(string $variableName);

    public function getVariableTyped(string $variableName, ?bool $deserializeValue = false): ?TypedValueInterface;

    public function getVariableLocalTyped(string $variableName, ?bool $deserializeValue = false): ?TypedValueInterface;

    public function getVariableNames(): array;

    public function getVariableNamesLocal(): array;

    public function setVariable(string $variableName, $value): void;

    public function setVariableLocal(string $variableName, $value): void;

    public function setVariables(array $variables): void;

    public function setVariablesLocal(array $variables): void;

    public function hasVariables(): bool;

    public function hasVariablesLocal(): bool;

    public function hasVariable(string $variableName): bool;

    public function hasVariableLocal(string $variableName): bool;

    /**
     * Removes the variable and creates a new
     * {@link HistoricVariableUpdateEntity}.
     */
    public function removeVariable(string $variableName): void;

    /**
     * Removes the local variable and creates a new
     * {@link HistoricVariableUpdateEntity}.
     */
    public function removeVariableLocal(string $variableName): void;

    /**
     * Removes the variables and creates a new
     * {@link HistoricVariableUpdateEntity} for each of them.
     */
    public function removeVariables(?array $variableNames = []): void;

    /**
     * Removes the local variables and creates a new
     * {@link HistoricVariableUpdateEntity} for each of them.
     */
    public function removeVariablesLocal(?array $variableNames = []): void;
}
