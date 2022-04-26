<?php

namespace Jabe\Engine\Impl\Core\Variable\Mapping\Value;

use Jabe\Engine\Delegate\VariableScopeInterface;

class ConstantValueProvider implements ParameterValueProviderInterface
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue(VariableScopeInterface $scope)
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }
}
