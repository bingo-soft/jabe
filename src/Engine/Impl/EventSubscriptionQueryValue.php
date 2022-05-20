<?php

namespace Jabe\Engine\Impl;

class EventSubscriptionQueryValue implements \Serializable
{
    protected $eventType;
    protected $eventName;

    public function __construct(string $eventName, string $eventType)
    {
        $this->eventName = $eventName;
        $this->eventType = $eventType;
    }

    public function serialize()
    {
        return json_encode([
            'eventName' => $this->eventName,
            'eventType' => $this->eventType
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->eventName = $json->eventName;
        $this->eventType = $json->eventType;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }
}
