<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Bpmn\Helper\CompensationUtil;
use Jabe\Engine\Impl\Bpmn\Parser\CompensateEventDefinition;
use Jabe\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class CompensationEventActivityBehavior extends FlowNodeActivityBehavior
{
    protected $compensateEventDefinition;

    public function __construct(CompensateEventDefinition $compensateEventDefinition)
    {
        $this->compensateEventDefinition = $compensateEventDefinition;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        $eventSubscriptions = $this->collectEventSubscriptions($execution);
        if (empty($eventSubscriptions)) {
            $this->leave($execution);
        } else {
            // async (waitForCompletion=false in bpmn) is not supported
            CompensationUtil::throwCompensationEvent($eventSubscriptions, $execution, false);
        }
    }

    protected function collectEventSubscriptions(ActivityExecutionInterface $execution): array
    {
        $activityRef = $compensateEventDefinition->getActivityRef();
        if ($activityRef !== null) {
            return CompensationUtil::collectCompensateEventSubscriptionsForActivity($execution, $activityRef);
        } else {
            return CompensationUtil::collectCompensateEventSubscriptionsForScope($execution);
        }
    }

    public function signal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        // join compensating executions -
        // only wait for non-event-scope executions cause a compensation event subprocess consume the compensation event and
        // do not have to compensate embedded subprocesses (which are still non-event-scope executions)
        if (($execution->getNonEventScopeExecutions())) {
            $this->leave($execution);
        } else {
            $execution->forceUpdate();
        }
    }
}
