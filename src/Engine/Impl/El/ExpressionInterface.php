<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\Delegate\{
    BaseDelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Engine\Delegate\ExpressionInterface as DelegateExpressionInterface;

interface ExpressionInterface extends DelegateExpressionInterface
{
    public function getValue(VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution);

    public function setValue($value, VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution): void;
}
