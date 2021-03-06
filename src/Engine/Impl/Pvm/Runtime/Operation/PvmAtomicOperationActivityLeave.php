<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Impl\Util\ActivityBehaviorUtil;
use Jabe\Engine\Impl\Bpmn\Behavior\FlowNodeActivityBehavior;
use Jabe\Engine\Impl\Pvm\{
    PvmException,
    PvmLogger
};
use Jabe\Engine\Impl\Pvm\Delegate\ActivityBehaviorInterface;
use Jabe\Engine\Impl\Pvm\Process\ActivityImpl;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationActivityLeave implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    //private final static PvmLogger LOG = PvmLogger.PVM_LOGGER;

    public function isAsync(PvmExecutionImpl $execution): bool
    {
        return false;
    }

    public function execute(PvmExecutionImpl $execution): void
    {
        $execution->activityInstanceDone();

        $activityBehavior = ActivityBehaviorUtil::getActivityBehavior($execution);

        if ($activityBehavior instanceof FlowNodeActivityBehavior) {
            $behavior = $activityBehavior;

            $activity = $execution->getActivity();
            $activityInstanceId = $execution->getActivityInstanceId();
            if (!empty($activityInstanceId)) {
                //LOG.debugLeavesActivityInstance(execution, activityInstanceId);
            }

            try {
                $behavior->doLeave($execution);
            } catch (\Exception $e) {
                throw new PvmException("couldn't leave activity <" . $activity->getProperty("type") . " id=\"" . $activity->getId() . "\" ...>: " . $e->getMessage(), $e);
            }
        } else {
            throw new PvmException("Behavior of current activity is not an instance of " . FlowNodeActivityBehavior::class . ". Execution " . $execution);
        }
    }

    public function getCanonicalName(): string
    {
        return "activity-leave";
    }

    public function isAsyncCapable(): bool
    {
        return false;
    }
}
