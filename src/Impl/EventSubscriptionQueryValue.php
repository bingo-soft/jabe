<?php

namespace Jabe\Impl;

class EventSubscriptionQueryValue
{
    protected $eventType;
    protected $eventName;

    public function __construct(?string $eventName, ?string $eventType)
    {
        $this->eventName = $eventName;
        $this->eventType = $eventType;
    }

    public function __serialize(): array
    {
        return [
            'eventName' => $this->eventName,
            'eventType' => $this->eventType
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->eventName = $data['eventName'];
        $this->eventType = $data['eventType'];
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(?string $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(?string $eventName): void
    {
        $this->eventName = $eventName;
    }
}
