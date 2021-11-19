<?php

namespace BpmPlatform\Engine\Impl\Core\Variable\Mapping\Value;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;

class NullValueProvider implements ParameterValueProviderInterface
{
    public function getValue(VariableScopeInterface $variableScope)
    {
        return null;
    }
}
