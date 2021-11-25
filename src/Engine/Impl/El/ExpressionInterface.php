<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Delegate\{
    BaseDelegateExecutionInterface,
    VariableScopeInterface
};
use BpmPlatform\Engine\Delegate\ExpressionInterface as DelegateExpressionInterface;

interface ExpressionInterface extends DelegateExpressionInterface
{
    public function getValue(VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution);

    public function setValue($value, VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution): void;
}
