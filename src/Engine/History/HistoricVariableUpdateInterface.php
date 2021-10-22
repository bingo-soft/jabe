<?php

namespace BpmPlatform\Engine\History;

use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

interface HistoricVariableUpdateInterface
{
    public function getVariableName(): string;

    /**
     * Returns the id of the corresponding variable instance.
     */
    public function getVariableInstanceId(): string;

    /**
     * Returns the type name of the variable
     *
     * @return the type name of the variable
     */
    public function getTypeName(): string;

    public function getValue();

    /**
     * @return the {@link TypedValue} for this variable update
     */
    public function getTypedValue(): TypedValueInterface;

    public function getRevision(): int;

    /**
     * If the variable value could not be loaded, this returns the error message.
     * @return an error message indicating why the variable value could not be loaded.
     */
    public function getErrorMessage(): string;

    /**
     * @return true if the detail historic variable update is created during the process instance start
     */
    public function isInitial(): bool;
}
