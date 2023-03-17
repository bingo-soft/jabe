<?php

namespace Tests\Bpmn\Event\Compensate;

class VariableEvent implements \Serializable
{
    public $variableName;
    public $variableValue;
    public $activityInstanceId;
    public $eventName;

    public function serialize()
    {
        return json_encode([
            'variableName' => $this->variableName,
            'variableValue' => $this->variableValue,
            'activityInstanceId' => $this->activityInstanceId,
            'eventName' => $this->eventName
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->variableName = $json->variableName;
        $this->variableValue = $json->variableValue;
        $this->activityInstanceId = $json->activityInstanceId;
        $this->eventName = $json->eventName;
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
