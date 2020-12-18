<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\ReceiveTaskBuilder;

interface ReceiveTaskInterface extends TaskInterface
{
    public function builder(): ReceiveTaskBuilder;

    public function getImplementation(): string;

    public function setImplementation(string $implementation): void;

    public function instantiate(): bool;

    public function setInstantiate(bool $instantiate): void;

    public function getMessage(): MessageInterface;

    public function setMessage(MessageInterface $message): void;

    public function getOperation(): OperationInterface;

    public function setOperation(OperationInterface $operation): void;
}
