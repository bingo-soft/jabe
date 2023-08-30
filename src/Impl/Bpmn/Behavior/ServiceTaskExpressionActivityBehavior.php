<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Delegate\ExpressionInterface;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ServiceTaskExpressionActivityBehavior extends TaskActivityBehavior
{
    protected $expression;
    protected $resultVariable;

    public function __construct(ExpressionInterface $expression, ?string $resultVariable)
    {
        parent::__construct();
        $this->expression = $expression;
        $this->resultVariable = $resultVariable;
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
        $scope = $this;
        $this->executeWithErrorPropagation($execution, function () use ($scope, $execution) {
            //getValue() can have side-effects, that's why we have to call it independently from the result variable
            $value = $scope->expression->getValue($execution);
            if ($scope->resultVariable !== null) {
                $execution->setVariable($scope->resultVariable, $value);
            }
            $scope->leave($execution);
            return null;
        });
    }
}
