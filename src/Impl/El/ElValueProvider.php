<?php

namespace Jabe\Impl\El;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;

class ElValueProvider implements ParameterValueProviderInterface
{
    protected $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getValue(?VariableScopeInterface $variableScope)
    {
        return $this->expression->getValue($variableScope);
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function isDynamic(): bool
    {
        return !$this->expression->isLiteralText();
    }
}
