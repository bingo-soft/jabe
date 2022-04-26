<?php

namespace Jabe\Engine\Impl\Event;

use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\EventSubscriptionEntity;

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
