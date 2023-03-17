<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Bpmn\Helper\CompensationUtil;
use Jabe\Impl\Bpmn\Parser\CompensateEventDefinition;
use Jabe\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class CompensationEventActivityBehavior extends FlowNodeActivityBehavior
{
    protected $compensateEventDefinition;

    public function __construct(CompensateEventDefinition $compensateEventDefinition)
    {
        parent::__construct();
        $this->compensateEventDefinition = $compensateEventDefinition;
    }

    public function execute(/*ActivityExecutionInterface*/$execution): void
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
        $activityRef = $this->compensateEventDefinition->getActivityRef();
        if ($activityRef !== null) {
            return CompensationUtil::collectCompensateEventSubscriptionsForActivity($execution, $activityRef);
        } else {
            return CompensationUtil::collectCompensateEventSubscriptionsForScope($execution);
        }
    }

    public function signal(/*ActivityExecutionInterface*/$execution, ?string $signalName = null, $signalData = null, array $processVariables = []): void
    {
        // join compensating executions -
        // only wait for non-event-scope executions cause a compensation event subprocess consume the compensation event and
        // do not have to compensate embedded subprocesses (which are still non-event-scope executions)
        if (empty($execution->getNonEventScopeExecutions())) {
            $this->leave($execution);
        } else {
            $execution->forceUpdate();
        }
    }
}
