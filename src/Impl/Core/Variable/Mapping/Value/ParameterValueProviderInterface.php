<?php

namespace Jabe\Impl\Core\Variable\Mapping\Value;

use Jabe\Delegate\VariableScopeInterface;

interface ParameterValueProviderInterface
{
    /**
     * @param variableScope the scope in which the value is to be resolved.
     * @return mixed the value
     */
    public function getValue(?VariableScopeInterface $variableScope);

    public function isDynamic(): bool;
}
