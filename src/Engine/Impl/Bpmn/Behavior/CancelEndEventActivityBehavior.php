<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Bpmn\Helper\CompensationUtil;
use Jabe\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Pvm\Process\ScopeImpl;
use Jabe\Engine\Impl\Util\EnsureUtil;

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
