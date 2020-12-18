<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\MultiInstanceLoopCharacteristicsBuilder;

interface MultiInstanceLoopCharacteristicsInterface extends LoopCharacteristicsInterface
{
    public function getLoopCardinality(): LoopCardinalityInterface;

    public function setLoopCardinality(LoopCardinalityInterface $loopCardinality): void;

    public function getLoopDataInputRef(): DataInputInterface;

    public function setLoopDataInputRef(DataInputInterface $loopDataInputRef): void;

    public function getLoopDataOutputRef(): DataOutputInterface;

    public function setLoopDataOutputRef(DataOutputInterface $loopDataOutputRef): void;

    public function getInputDataItem(): InputDataItemInterface;

    public function setInputDataItem(InputDataItemInterface $inputDataItem): void;

    public function getOutputDataItem(): OutputDataItemInterface;

    public function setOutputDataItem(OutputDataItemInterface $outputDataItem): void;

    public function getComplexBehaviorDefinitions(): array;

    public function getCompletionCondition(): CompletionConditionInterface;

    public function setCompletionCondition(CompletionConditionInterface $completionCondition): void;

    public function isSequential(): bool;

    public function setSequential(bool $sequential): void;

    public function getBehavior(): string;

    public function setBehavior(string $behavior): void;

    public function getOneBehaviorEventRef(): EventDefinitionInterface;

    public function setOneBehaviorEventRef(EventDefinitionInterface $oneBehaviorEventRef): void;

    public function getNoneBehaviorEventRef(): EventDefinitionInterface;

    public function setNoneBehaviorEventRef(EventDefinitionInterface $noneBehaviorEventRef): void;

    public function getCollection(): string;

    public function setCollection(string $collection): void;

    public function getElementVariable(): string;

    public function setElementVariable(string $variableName): void;

    public function isAsyncBefore(): bool;

    public function setAsyncBefore(bool $isAsyncBefore): void;

    public function isAsyncAfter(): bool;

    public function setAsyncAfter(bool $isAsyncAfter): void;

    public function isExclusive(): bool;

    public function setExclusive(bool $isExclusive): bool;

    public function builder(): MultiInstanceLoopCharacteristicsBuilder;
}
