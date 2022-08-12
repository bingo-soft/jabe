<?php

namespace Jabe\Impl\JobExecutor;

class EventSubscriptionJobConfiguration implements JobHandlerConfigurationInterface
{
    protected $eventSubscriptionId;

    public function __construct(string $eventSubscriptionId)
    {
        $this->eventSubscriptionId = $eventSubscriptionId;
    }

    public function getEventSubscriptionId(): string
    {
        return $this->eventSubscriptionId;
    }

    public function toCanonicalString(): string
    {
        return $this->eventSubscriptionId;
    }

    public function __toString()
    {
        return $this->toCanonicalString();
    }
}
