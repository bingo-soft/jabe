<?php

namespace Jabe\Impl;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    VariableScopeInterface
};

interface ConditionInterface
{
    /**
     * Evaluates the condition and returns the result.
     *
     * @param scope the variable scope which can differ of the execution
     * @param execution the execution which is used to evaluate the condition
     * @return bool the result
     */
    public function evaluate(?VariableScopeInterface $scope, ?DelegateExecutionInterface $execution = null): bool;

    /**
     * Tries to evaluate the condition. If the property which is used in the condition does not exist
     * false will be returned.
     *
     * @param scope the variable scope which can differ of the execution
     * @param execution the execution which is used to evaluate the condition
     * @return bool the result
     */
    public function tryEvaluate(?VariableScopeInterface $scope, ?DelegateExecutionInterface $execution = null): bool;
}
