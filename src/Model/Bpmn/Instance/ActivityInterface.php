<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ActivityInterface extends FlowNodeInterface, InteractionNodeInterface
{
    public function isForCompensation(): bool;

    public function setForCompensation(bool $isForCompensation): void;

    public function getStartQuantity(): int;

    public function setStartQuantity(int $startQuantity): void;

    public function getCompletionQuantity(): int;

    public function setCompletionQuantity(int $completionQuantity): void;

    public function getDefault(): SequenceFlowInterface;

    public function setDefault(SequenceFlowInterface $defaultFlow): void;

    public function getIoSpecification(): IoSpecificationInterface;

    public function setIoSpecification(IoSpecificationInterface $ioSpecification): void;

    public function getProperties(): array;

    public function addProperty(PropertyInterface $property): void;

    public function getDataInputAssociations(): array;

    public function getDataOutputAssociations(): array;

    public function getResourceRoles(): array;

    public function getLoopCharacteristics(): LoopCharacteristicsInterface;

    public function setLoopCharacteristics(LoopCharacteristicsInterface $loopCharacteristics): void;
}
