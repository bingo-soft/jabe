<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\IoParameter;
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;

class ElValueProvider implements ParameterValueProviderInterface
{
    protected $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getValue(VariableScopeInterface $variableScope)
    {
        return $this->expression->getValue($variableScope);
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->expression;
    }
}
