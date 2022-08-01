<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Impl\Pvm\Delegate\CompositeActivityBehaviorInterface;
use Jabe\Engine\Impl\Pvm\Runtime\{
    CompensationBehavior,
    PvmExecutionImpl
};
use Jabe\Engine\Impl\Core\Instance\CoreExecution;

abstract class PvmAtomicOperationActivityInstanceStart extends AbstractPvmEventAtomicOperation
{
    protected function eventNotificationsStarted(CoreExecution $execution): CoreExecution
    {
        $execution->incrementSequenceCounter();
        $execution->activityInstanceStarting();
        $execution->enterActivityInstance();
        $execution->setTransition(null);

        return $execution;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        // hack around execution tree structure not being in sync with activity instance concept:
        // if we start a scope activity, remember current activity instance in parent
        $parent = $execution->getParent();
        $activity = $execution->getActivity();
        if ($parent !== null && $execution->isScope() && $activity->isScope() && $this->canHaveChildScopes($execution)) {
            $parent->setActivityInstanceId($execution->getActivityInstanceId());
        }
    }

    protected function canHaveChildScopes(PvmExecutionImpl $execution): bool
    {
        $activity = $execution->getActivity();
        return $activity->getActivityBehavior() instanceof CompositeActivityBehaviorInterface
            || CompensationBehavior::isCompensationThrowing($execution);
    }
}
