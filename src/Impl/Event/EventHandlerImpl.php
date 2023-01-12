<?php

namespace Jabe\Impl\Event;

use Jabe\Impl\Bpmn\Behavior\EventSubProcessStartEventActivityBehavior;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\EventSubscriptionEntity;
use Jabe\Impl\Util\EnsureUtil;

class EventHandlerImpl implements EventHandlerInterface
{
    private $eventType;

    public function __construct(EventType $eventType)
    {
        $this->eventType = $eventType;
    }

    public function handleIntermediateEvent(
        EventSubscriptionEntity $eventSubscription,
        $payload,
        $localPayload,
        CommandContext $commandContext
    ): void {
        $execution = $eventSubscription->getExecution();
        $activity = $eventSubscription->getActivity();

        EnsureUtil::ensureNotNull(
            "Error while sending signal for event subscription '" . $eventSubscription->getId() . "': "
            . "no activity associated with event subscription",
            "activity",
            $activity
        );

        if (is_array($payload) && !empty($payload)) {
            $execution->setVariables($payload);
        }

        if (is_array($localPayload) && !empty($localPayload)) {
            $execution->setVariablesLocal($localPayload);
        }

        if ($activity == $execution->getActivity()) {
            $execution->signal("signal", null);
        } else {
            // hack around the fact that the start event is referenced by event subscriptions for event subprocesses
            // and not the subprocess itself
            if ($activity->getActivityBehavior() instanceof EventSubProcessStartEventActivityBehavior) {
                $activity = $activity->getFlowScope();
            }

            $execution->executeEventHandlerActivity($activity);
        }
    }

    public function handleEvent(
        EventSubscriptionEntity $eventSubscription,
        $payload,
        $localPayload,
        ?string $businessKey,
        CommandContext $commandContext
    ): void {
        $this->handleIntermediateEvent($eventSubscription, $payload, $localPayload, $commandContext);
    }

    public function getEventHandlerType(): ?string
    {
        return $this->eventType->name();
    }
}
