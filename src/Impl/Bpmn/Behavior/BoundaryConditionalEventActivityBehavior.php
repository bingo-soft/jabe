<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Bpmn\Parser\ConditionalEventDefinition;
use Jabe\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use Jabe\Impl\Pvm\Runtime\{
    ActivityInstanceState,
    PvmExecutionImpl
};

class BoundaryConditionalEventActivityBehavior extends BoundaryEventActivityBehavior implements ConditionalEventBehaviorInterface
{
    protected $conditionalEvent;

    public function __construct(ConditionalEventDefinition $conditionalEvent)
    {
        $this->conditionalEvent = $conditionalEvent;
    }

    public function getConditionalEventDefinition(): ConditionalEventDefinition
    {
        return $this->conditionalEvent;
    }

    public function leaveOnSatisfiedCondition(EventSubscriptionEntity $eventSubscription, VariableEvent $variableEvent): void
    {
        $execution = $eventSubscription->getExecution();

        if (
            $execution !== null &&
            !$execution->isEnded() &&
            $execution->isScope() &&
            $this->conditionalEvent->tryEvaluate($variableEvent, $execution)
        ) {
            $execution->executeEventHandlerActivity($eventSubscription->getActivity());
        }
    }
}
