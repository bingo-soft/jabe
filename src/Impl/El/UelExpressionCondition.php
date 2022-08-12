<?php

namespace Jabe\Impl\El;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Impl\ConditionInterface;

class UelExpressionCondition implements ConditionInterface
{

    protected $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function evaluate(?VariableScopeInterface $scope, DelegateExecutionInterface $execution): bool
    {
        $scope = $scope ?? $execution;
        $result = $this->expression->getValue($scope, $execution);
        return $result;
    }

    public function tryEvaluate(?VariableScopeInterface $scope, DelegateExecutionInterface $execution): bool
    {
        $result = $this->evaluate($scope, $execution);
        return $result;
    }
}
