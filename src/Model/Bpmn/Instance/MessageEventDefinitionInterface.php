<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface MessageEventDefinitionInterface extends EventDefinitionInterface
{
    public function getMessage(): ?MessageInterface;

    public function setMessage(MessageInterface $message): void;

    public function getOperation(): ?OperationInterface;

    public function setOperation(OperationInterface $operation): void;

    public function getClass(): string;

    public function setClass(string $class): void;

    public function getDelegateExpression(): string;

    public function setDelegateExpression(string $expression): void;

    public function getExpression(): string;

    public function setExpression(string $expression): void;

    public function getResultVariable(): string;

    public function setResultVariable(string $resultVariable): void;

    public function getTopic(): string;

    public function setTopic(string $topic): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function getTaskPriority(): string;

    public function setTaskPriority(string $taskPriority): void;
}
