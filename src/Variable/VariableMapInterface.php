<?php

namespace Jabe\Variable;

use Jabe\Variable\Context\VariableContextInterface;
use Jabe\Variable\Value\TypedValueInterface;

interface VariableMapInterface
{
    // fluent api for collecting variables ////////////////////////

    public function putValue(string $name, $value): VariableMapInterface;

    public function putValueTyped(string $name, TypedValueInterface $value): VariableMapInterface;

    // retrieving variables ///////////////////////////////////////

    public function getValue(string $name, string $type);

    public function getValueTyped(string $name);

    /**
     * Interprets the variable map as variable context
     *
     * @return A VariableContext which is capable of resolving all variables in the map
     */
    public function asVariableContext(): VariableContextInterface;
}
