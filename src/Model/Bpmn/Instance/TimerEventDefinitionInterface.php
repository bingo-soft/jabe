<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface TimerEventDefinitionInterface extends EventDefinitionInterface
{
    public function getTimeDate(): ?TimeDateInterface;

    public function setTimeDate(TimeDateInterface $timeDate): void;

    public function getTimeDuration(): ?TimeDurationInterface;

    public function setTimeDuration(TimeDurationInterface $timeDuration): void;

    public function getTimeCycle(): ?TimeCycleInterface;

    public function setTimeCycle(TimeCycleInterface $timeCycle): void;
}
