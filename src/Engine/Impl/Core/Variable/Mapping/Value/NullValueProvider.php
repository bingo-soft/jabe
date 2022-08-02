<?php

namespace Jabe\Engine\Impl\Core\Variable\Mapping\Value;

use Jabe\Engine\Delegate\VariableScopeInterface;

class NullValueProvider implements ParameterValueProviderInterface
{
    public function getValue(VariableScopeInterface $variableScope)
    {
        return null;
    }

    public function isDynamic(): bool
    {
        return false;
    }
}
