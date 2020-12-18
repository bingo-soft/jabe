<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface OperationInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getImplementationRef(): string;

    public function setImplementationRef(string $implementationRef): void;

    public function getInMessage(): MessageInterface;

    public function setInMessage(MessageInterface $message): void;

    public function getOutMessage(): MessageInterface;

    public function setOutMessage(MessageInterface $message): void;

    public function getErrors(): array;
}
