<?php

namespace Jabe\Impl\Event;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\EventSubscriptionEntity;
use Jabe\Impl\Bpmn\Behavior\ConditionalEventBehaviorInterface;
use Jabe\Impl\Core\Variable\Event\VariableEvent;

class ConditionalEventHandler implements EventHandlerInterface
{
    public function getEventHandlerType(): ?string
    {
        return EventType::conditional()->name();
    }

    public function handleEvent(
        EventSubscriptionEntity $eventSubscription,
        $payload,
        $localPayload,
        ?string $businessKey,
        CommandContext $commandContext
    ): void {
        $variableEvent = null;
        if ($payload === null || $payload instanceof VariableEvent) {
            $variableEvent = $payload;
        } else {
            throw new ProcessEngineException("Payload have to be " . VariableEvent::class . ", to evaluate condition.");
        }

        $activity = $eventSubscription->getActivity();
        $activityBehavior = $activity->getActivityBehavior();
        if ($activityBehavior instanceof ConditionalEventBehaviorInterface) {
            $conditionalBehavior = $activityBehavior;
            $conditionalBehavior->leaveOnSatisfiedCondition($eventSubscription, $variableEvent);
        } else {
            throw new ProcessEngineException("Conditional Event has not correct behavior");
        }
    }
}
