<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Bpmn\Parser\ConditionalEventDefinition;
use BpmPlatform\Engine\Impl\Core\Variable\Event\VariableEvent;
use BpmPlatform\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use BpmPlatform\Engine\Impl\Pvm\Runtime\{
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
            $execution != null &&
            !$execution->isEnded() &&
            $execution->isScope() &&
            $conditionalEvent->tryEvaluate($variableEvent, $execution)
        ) {
            $execution->executeEventHandlerActivity($eventSubscription->getActivity());
        }
    }
}
