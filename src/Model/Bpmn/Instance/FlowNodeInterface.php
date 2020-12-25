<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\QueryInterface;
use BpmPlatform\Model\Bpmn\Builder\AbstractBaseElementBuilder;

interface FlowNodeInterface extends FlowElementInterface
{
    public function builder(): AbstractBaseElementBuilder;

    public function getIncoming(): array;

    public function addIncoming(SequenceFlowInterface $element): void;

    public function getOutgoing(): array;

    public function addOutgoing(SequenceFlowInterface $element): void;

    public function getPreviousNodes(): QueryInterface;

    public function getSucceedingNodes(): QueryInterface;

    public function isAsyncBefore(): bool;

    public function setAsyncBefore(bool $isAsyncBefore): void;

    public function isAsyncAfter(): bool;

    public function setAsyncAfter(bool $isAsyncAfter): void;

    public function isExclusive(): bool;

    public function setExclusive(bool $isExclusive): void;

    public function getJobPriority(): string;

    public function setJobPriority(string $jobPriority): void;
}
