<?php

namespace Jabe\Engine\Impl\Pvm\Delegate;

interface CompositeActivityBehaviorInterface extends ActivityBehaviorInterface
{
    /**
     * Invoked when an execution is ended within the scope of the composite
     *
     * @param scopeExecution scope execution for the activity which defined the behavior
     * @param endedExecution the execution which ended
     */
    public function concurrentChildExecutionEnded(?ActivityExecutionInterface $scopeExecution, ActivityExecutionInterface $endedExecution): void;

    public function complete(ActivityExecutionInterface $scopeExecution): void;
}
