<?php

namespace BpmPlatform\Engine\Impl\Core\Variable\Mapping\Value;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;

interface ParameterValueProviderInterface
{
    /**
     * @param variableScope the scope in which the value is to be resolved.
     * @return the value
     */
    public function getValue(VariableScopeInterface $variableScope);
}
