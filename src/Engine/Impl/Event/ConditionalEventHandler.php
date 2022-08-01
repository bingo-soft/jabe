<?php

namespace Jabe\Engine\Impl\Event;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\EventSubscriptionEntity;
use Jabe\Engine\Impl\Bpmn\Behavior\ConditionalEventBehaviorInterface;
use Jabe\Engine\Impl\Core\Variable\Event\VariableEvent;

class ConditionalEventHandler implements EventHandlerInterface
{
    public function getEventHandlerType(): string
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
