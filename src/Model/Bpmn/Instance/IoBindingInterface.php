<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface IoBindingInterface extends BaseElementInterface
{
    public function getOperation(): OperationInterface;

    public function setOperation(OperationInterface $operation): void;

    public function getInputData(): DataInputInterface;

    public function setInputData(DataInputInterface $inputData): void;

    public function getOutputData(): DataOutputInterface;

    public function setOutputData(DataOutputInterface $outputData): void;
}
