<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\Pvm\Runtime\{
    LegacyBehavior,
    PvmExecutionImpl
};

abstract class PvmAtomicOperationCancelActivity implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    public function execute(PvmExecutionImpl $execution, ...$args): void
    {
        // Assumption: execution is scope
        $cancellingActivity = $execution->getNextActivity();
        $execution->setNextActivity(null);

        // first, cancel and destroy the current scope
        $execution->setActive(true);

        $propagatingExecution = null;

        if (LegacyBehavior::isConcurrentScope($execution)) {
            // this is legacy behavior
            LegacyBehavior::cancelConcurrentScope($execution, $cancellingActivity->getEventScope());
            $propagatingExecution = $execution;
        } else {
            // Unlike PvmAtomicOperationTransitionDestroyScope this needs to use delete() (instead of destroy() and remove()).
            // The reason is that PvmAtomicOperationTransitionDestroyScope is executed when a scope (or non scope) is left using
            // a sequence flow. In that case the execution will have completed all the work inside the current activity
            // and will have no more child executions. In PvmAtomicOperationCancelScope the scope is cancelled due to
            // a boundary event firing. In that case the execution has not completed all the work in the current scope / activity
            // and it is necessary to delete the complete hierarchy of executions below and including the execution itself.
            $execution->deleteCascade("Cancel scope activity " . $cancellingActivity . " executed.");
            $propagatingExecution = $execution->getParent();
        }

        $propagatingExecution->setActivity($cancellingActivity);
        $propagatingExecution->setActive(true);
        $propagatingExecution->setEnded(false);
        $this->activityCancelled($propagatingExecution);
    }

    abstract protected function activityCancelled(PvmExecutionImpl $execution): void;

    public function isAsync(PvmExecutionImpl $execution): bool
    {
        return false;
    }
}
