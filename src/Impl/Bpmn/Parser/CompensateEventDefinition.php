<?php

namespace Jabe\Impl\Bpmn\Parser;

class CompensateEventDefinition
{
    protected $activityRef;
    protected $waitForCompletion;

    public function __serialize(): array
    {
        return [
            'activityRef' => $this->activityRef,
            'waitForCompletion' => $this->waitForCompletion
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->activityRef = $data['activityRef'];
        $this->waitForCompletion = $data['waitForCompletion'];
    }

    public function getActivityRef(): ?string
    {
        return $this->activityRef;
    }

    public function setActivityRef(?string $activityRef): void
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
