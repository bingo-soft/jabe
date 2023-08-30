<?php

namespace Tests\Bpmn\Event\Compensate;

class VariableEvent
{
    public $variableName;
    public $variableValue;
    public $activityInstanceId;
    public $eventName;

    public function __serialize(): array
    {
        return [
            'variableName' => $this->variableName,
            'variableValue' => $this->variableValue,
            'activityInstanceId' => $this->activityInstanceId,
            'eventName' => $this->eventName
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->variableName = $data['variableName'];
        $this->variableValue = $data['variableValue'];
        $this->activityInstanceId = $data['activityInstanceId'];
        $this->eventName = $data['eventName'];
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariableValue()
    {
        return $this->variableValue;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }
}
