<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Engine\Variable\{
    VariableMapInterface,
    Variables
};
use Jabe\Engine\Variable\Value\TypedValueInterface;

class StartProcessVariableScope implements VariableScopeInterface
{
    private static $INSTANCE;

    private static $EMPTY_VARIABLE_MAP;

    public function __construct()
    {
        if (self::$EMPTY_VARIABLE_MAP === null) {
            self::$EMPTY_VARIABLE_MAP = Variables::fromMap([]);
        }
    }

    /**
     * Since a StartProcessVariableScope has no state, it's safe to use the same
     * instance to prevent too many useless instances created.
     */
    public static function getSharedInstance(): StartProcessVariableScope
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new StartProcessVariableScope();
        }
        return self::$INSTANCE;
    }

    public function getVariableScopeKey(): string
    {
        return "scope";
    }

    public function getVariables(): array
    {
        return self::$EMPTY_VARIABLE_MAP;
    }

    public function getVariablesTyped(?bool $deserializeValues = true): VariableMapInterface
    {
        return $this->getVariables();
    }

    public function getVariablesLocal(): array
    {
        return self::$EMPTY_VARIABLE_MAP;
    }

    public function getVariablesLocalTyped(?bool $deserializeValues = true): VariableMapInterface
    {
        return $this->getVariablesLocal();
    }

    public function getVariable(string $variableName)
    {
        return null;
    }

    public function getVariableLocal(string $variableName)
    {
        return null;
    }

    public function getVariableTyped(string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface
    {
        return null;
    }

    public function getVariableLocalTyped(string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface
    {
        return null;
    }

    public function getVariableNames(): array
    {
        return [];
    }

    public function getVariableNamesLocal(): array
    {
        return [];
    }

    public function setVariable(string $variableName, $value): void
    {
        throw new \Exception("No execution active, no variables can be set");
    }

    public function setVariableLocal(string $variableName, $value): void
    {
        throw new \Exception("No execution active, no variables can be set");
    }

    public function setVariables(array $variables): void
    {
        throw new \Exception("No execution active, no variables can be set");
    }

    public function setVariablesLocal(array $variables): void
    {
        throw new \Exception("No execution active, no variables can be set");
    }

    public function hasVariables(): bool
    {
        return false;
    }

    public function hasVariablesLocal(): bool
    {
        return false;
    }

    public function hasVariable(string $variableName): bool
    {
        return false;
    }

    public function hasVariableLocal(string $variableName): bool
    {
        return false;
    }

    /**
     * Removes the variable and creates a new
     * HistoricVariableUpdateEntity.
     */
    public function removeVariable(string $variableName): void
    {
        throw new \Exception("No execution active, no variables can be removed");
    }

    /**
     * Removes the local variable and creates a new
     * HistoricVariableUpdateEntity.
     */
    public function removeVariableLocal(string $variableName): void
    {
        throw new \Exception("No execution active, no variables can be removed");
    }

    /**
     * Removes the variables and creates a new
     * HistoricVariableUpdateEntity for each of them.
     */
    public function removeVariables(?array $variableNames = []): void
    {
        throw new \Exception("No execution active, no variables can be removed");
    }

    /**
     * Removes the local variables and creates a new
     * HistoricVariableUpdateEntity for each of them.
     */
    public function removeVariablesLocal(?array $variableNames = []): void
    {
        throw new \Exception("No execution active, no variables can be removed");
    }

    public function getVariableInstances(): array
    {
        return [];
    }

    public function getVariableInstance(string $name): ?CoreVariableInstanceInterface
    {
        return null;
    }

    public function getVariableInstancesLocal(): array
    {
        return [];
    }

    public function getVariableInstanceLocal(string $name): ?CoreVariableInstanceInterface
    {
        return null;
    }
}
