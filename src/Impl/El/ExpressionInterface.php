<?php

namespace Jabe\Impl\El;

use Jabe\Delegate\{
    BaseDelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Delegate\ExpressionInterface as DelegateExpressionInterface;

interface ExpressionInterface extends DelegateExpressionInterface
{
    public function getValue(VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution = null);

    public function setValue($value, VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution = null): void;
}
