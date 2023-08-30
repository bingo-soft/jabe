<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmLogger
};
use Jabe\Impl\Pvm\Delegate\CompositeActivityBehaviorInterface;
use Jabe\Impl\Pvm\Runtime\{
    CompensationBehavior,
    LegacyBehavior,
    PvmExecutionImpl
};
use Jabe\Impl\Core\Instance\CoreExecution;

abstract class PvmAtomicOperationActivityInstanceEnd extends AbstractPvmEventAtomicOperation
{
    //private final static PvmLogger LOG = ProcessEngineLogger.PVM_LOGGER;

    protected function eventNotificationsStarted(CoreExecution $execution, ...$args): CoreExecution
    {
        $execution->incrementSequenceCounter();

        // hack around execution tree structure not being in sync with activity instance concept:
        // if we end a scope activity, take remembered activity instance from parent and set on
        // execution before calling END listeners.
        $parent = $execution->getParent();
        $activity = $execution->getActivity();
        if (
            $parent !== null && $execution->isScope() &&
            $activity !== null && $activity->isScope() &&
            ($activity->getActivityBehavior() instanceof CompositeActivityBehaviorInterface ||
            (CompensationBehavior::isCompensationThrowing($execution)) &&
            !LegacyBehavior::isCompensationThrowing($execution))
        ) {
            //LOG.debugLeavesActivityInstance(execution, $execution->getActivityInstanceId());
            // use remembered activity instance id from parent
            $execution->setActivityInstanceId($parent->getActivityInstanceId());
            // make parent go one scope up.
            $parent->leaveActivityInstance();
        }
        $execution->setTransition(null);
        return $execution;
    }

    protected function eventNotificationsFailed(CoreExecution $execution, \Exception $e): void
    {
        $execution->activityInstanceEndListenerFailure();
        parent::eventNotificationsFailed($execution, $e);
    }

    protected function isSkipNotifyListeners(CoreExecution $execution): bool
    {
        // listeners are skipped if this execution is not part of an activity instance.
        // or if the end listeners for this activity instance were triggered before already and failed.
        return $execution->hasFailedOnEndListeners() ||
            $execution->getActivityInstanceId() === null;
    }
}
