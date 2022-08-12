<?php

namespace Jabe\Impl\Core\Variable;

interface SetVariableFunctionInterface
{
    public function apply(string $variableName, $variableValue): void;
}
