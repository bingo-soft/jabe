<?php

namespace Tests\Bpmn\ExecutionListener;

class RecordedEvent
{
    public $activityId;
    public $eventName;
    public $activityName;
    public $parameter;
    public $activityInstanceId;
    public $transitionId;
    private $canceled = false;
    public $executionId;

    public function __construct(string $activityId, ?string $activityName, string $eventName, ?string $parameter, string $activityInstanceId, ?string $transitionId, bool $canceled, string $executionId)
    {
        $this->activityId = $activityId;
        $this->activityName = $activityName;
        $this->parameter = $parameter;
        $this->eventName = $eventName;
        $this->activityInstanceId = $activityInstanceId;
        $this->transitionId = $transitionId;
        $this->canceled = $canceled;
        $this->executionId = $executionId;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }


    public function getActivityName(): ?string
    {
        return $this->activityName;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }

    public function getTransitionId(): string
    {
        return $this->transitionId;
    }

    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }
}
