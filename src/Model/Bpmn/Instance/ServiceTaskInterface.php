<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\ServiceTaskBuilder;

interface ServiceTaskInterface extends TaskInterface
{
    public function builder(): ServiceTaskBuilder;

    public function getImplementation(): string;

    public function setImplementation(string $implementation): void;

    public function getOperation(): OperationInterface;

    public function setOperation(OperationInterface $operation): void;

    public function getClass(): string;

    public function setClass(string $class): void;

    public function getDelegateExpression(): string;

    public function setDelegateExpression(string $expression): void;

    public function getExpression(): string;

    public function setExpression(string $expression): void;

    public function getResultVariable(): string;

    public function setResultVariable(string $resultVariable): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function getTopic(): string;

    public function setTopic(string $topic): void;

    public function getTaskPriority(): string;

    public function setTaskPriority(string $taskPriority): void;
}
