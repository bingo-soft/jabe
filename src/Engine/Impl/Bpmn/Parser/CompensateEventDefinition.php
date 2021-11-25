<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

class CompensateEventDefinition implements \Serializable
{
    protected $activityRef;
    protected $waitForCompletion;

    public function serialize()
    {
        return json_encode([
            'activityRef' => $this->activityRef,
            'waitForCompletion' => $this->waitForCompletion
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->activityRef = $json->activityRef;
        $this->waitForCompletion = $json->waitForCompletion;
    }

    public function getActivityRef(): ?string
    {
        return $this->activityRef;
    }

    public function setActivityRef(string $activityRef): void
    {
        $this->activityRef = $activityRef;
    }

    public function isWaitForCompletion(): bool
    {
        return $this->waitForCompletion;
    }

    public function setWaitForCompletion(bool $waitForCompletion): void
    {
        $this->waitForCompletion = $waitForCompletion;
    }
}
