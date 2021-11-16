<?php

namespace BpmPlatform\Engine\Impl\Pvm\Delegate;

interface ModificationObserverBehaviorInterface extends ActivityBehaviorInterface
{
    /**
     * Implement to customize initialization of the scope. Called with the
     * scope execution already created. Implementations may set variables, etc.
     * Implementations should provide return as many executions as there are requested by the argument.
     * Valid number of instances are >= 0.
     */
    public function initializeScope(ActivityExecutionInterface $scopeExecution, int $nrOfInnerInstances): array;

    /**
     * Returns an execution that can be used to execute an activity within that scope.
     * May reorganize other executions in that scope (e.g. implement to override the default pruning behavior).
     */
    public function createInnerInstance(ActivityExecutionInterface $scopeExecution): ActivityExecutionInterface;

    /**
     * implement to destroy an execution in this scope and handle the scope's reorganization
     * (e.g. implement to override the default pruning behavior). The argument execution is not yet removed.
     */
    public function destroyInnerInstance(ActivityExecutionInterface $concurrentExecution): void;
}
