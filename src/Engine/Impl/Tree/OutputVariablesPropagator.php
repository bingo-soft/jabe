<?php

namespace BpmPlatform\Engine\Impl\Tree;

use BpmPlatform\Engine\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    SubProcessActivityBehaviorInterface
};
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class OutputVariablesPropagator implements TreeVisitorInterface
{
    public function visit($execution): void
    {
        if ($this->isProcessInstanceOfSubprocess($execution)) {
            $superExecution = $execution->getSuperExecution();
            $activity = $superExecution->getActivity();
            $subProcessActivityBehavior = $activity->getActivityBehavior();
            $subProcessActivityBehavior->passOutputVariables($superExecution, $execution);
        }
    }

    protected function isProcessInstanceOfSubprocess(ActivityExecutionInterface $execution): bool
    {
        return $execution->isProcessInstanceExecution() && $execution->getSuperExecution() != null;
    }
}
