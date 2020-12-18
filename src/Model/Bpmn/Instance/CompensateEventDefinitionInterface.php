<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface CompensateEventDefinitionInterface extends EventDefinitionInterface
{
    public function isWaitForCompletion(): bool;

    public function setWaitForCompletion(bool $isWaitForCompletion): void;

    public function getActivity(): ActivityInterface;

    public function setActivity(ActivityInterface $activity): void;
}
