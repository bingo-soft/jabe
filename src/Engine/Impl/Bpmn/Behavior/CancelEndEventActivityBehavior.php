<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Bpmn\Helper\CompensationUtil;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use BpmPlatform\Engine\Impl\Pvm\PvmActivityInterface;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\ScopeImpl;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class CancelEndEventActivityBehavior extends AbstractBpmnActivityBehavior
{
    protected $cancelBoundaryEvent;

    public function execute(ActivityExecutionInterface $execution): void
    {
        EnsureUtil::ensureNotNull("Could not find cancel boundary event for cancel end event " . $execution->getActivity(), "cancelBoundaryEvent", $this->cancelBoundaryEvent);

        $compensateEventSubscriptions = CompensationUtil::collectCompensateEventSubscriptionsForScope($execution);

        if (empty($compensateEventSubscriptions)) {
            $this->leave($execution);
        } else {
            CompensationUtil::throwCompensationEvent($compensateEventSubscriptions, $execution, false);
        }
    }

    public function doLeave(ActivityExecutionInterface $execution): void
    {
        // continue via the appropriate cancel boundary event
        $eventScope = $this->cancelBoundaryEvent->getEventScope();

        $boundaryEventScopeExecution = $execution->findExecutionForFlowScope($eventScope);
        $boundaryEventScopeExecution->executeActivity($cancelBoundaryEvent);
    }

    public function signal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        // join compensating executions
        if (!$execution->hasChildren()) {
            $this->leave($execution);
        } else {
            $execution->forceUpdate();
        }
    }

    public function setCancelBoundaryEvent(PvmActivityInterface $cancelBoundaryEvent): void
    {
        $this->cancelBoundaryEvent = $cancelBoundaryEvent;
    }

    public function getCancelBoundaryEvent(): PvmActivityInterface
    {
        return $this->cancelBoundaryEvent;
    }
}
