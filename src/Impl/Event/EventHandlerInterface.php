<?php

namespace Jabe\Impl\Event;

use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\EventSubscriptionEntity;

interface EventHandlerInterface
{
    public function getEventHandlerType(): ?string;

    public function handleEvent(
        EventSubscriptionEntity $eventSubscription,
        $payload,
        $localPayload,
        ?string $businessKey,
        CommandContext $commandContext
    ): void;
}
