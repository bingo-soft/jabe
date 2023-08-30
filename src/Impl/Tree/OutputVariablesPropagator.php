<?php

namespace Jabe\Impl\Tree;

use Jabe\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    SubProcessActivityBehaviorInterface
};

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
        return $execution->isProcessInstanceExecution() && $execution->getSuperExecution() !== null;
    }
}
