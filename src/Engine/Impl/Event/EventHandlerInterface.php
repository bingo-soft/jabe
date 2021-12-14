<?php

namespace BpmPlatform\Engine\Impl\Event;

use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\EventSubscriptionEntity;

interface EventHandlerInterface
{
    public function getEventHandlerType(): string;

    public function handleEvent(
        EventSubscriptionEntity $eventSubscription,
        $payload,
        $localPayload,
        ?string $businessKey,
        CommandContext $commandContext
    ): void;
}
