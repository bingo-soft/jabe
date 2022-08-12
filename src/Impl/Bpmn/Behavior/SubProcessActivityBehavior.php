<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Util\EnsureUtil;
use Jabe\Impl\Bpmn\Helper\{
    BpmnProperties,
    CompensationUtil
};
use Jabe\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    CompositeActivityBehaviorInterface
};

class SubProcessActivityBehavior extends AbstractBpmnActivityBehavior implements CompositeActivityBehaviorInterface
{
    public function execute(ActivityExecutionInterface $execution): void
    {
        $activity = $execution->getActivity();
        $initialActivity = $activity->getProperties()->get(BpmnProperties::INITIAL_ACTIVITY);

        EnsureUtil::ensureNotNull("No initial activity found for subprocess " . $execution->getActivity()->getId(), "initialActivity", $this->initialActivity);

        $execution->executeActivity($initialActivity);
    }

    public function concurrentChildExecutionEnded(ActivityExecutionInterface $scopeExecution, ActivityExecutionInterface $endedExecution): void
    {
        // join
        $endedExecution->remove();
        $scopeExecution->tryPruneLastConcurrentChild();
        $scopeExecution->forceUpdate();
    }

    public function complete(ActivityExecutionInterface $scopeExecution): void
    {
        $this->leave($scopeExecution);
    }

    public function doLeave(ActivityExecutionInterface $execution): void
    {
        CompensationUtil::createEventScopeExecution($execution);
        parent::doLeave($execution);
    }
}
