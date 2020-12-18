<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface SignalEventDefinitionInterface extends EventDefinitionInterface
{
    public function getSignal(): SignalInterface;

    public function setSignal(SignalInterface $signal): void;

    public function isAsync(): bool;

    public function setAsync(bool $async): void;
}
