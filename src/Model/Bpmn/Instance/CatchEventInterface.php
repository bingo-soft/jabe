<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface CatchEventInterface extends EventInterface
{
    public function isParallelMultiple(): bool;

    public function setParallelMultiple(bool $parallelMultiple): void;

    public function getDataOutputs(): array;

    public function getDataOutputAssociations(): array;

    public function getOutputSet(): OutputSetInterface;

    public function setOutputSet(OutputSetInterface $outputSet): void;

    public function getEventDefinitions(): array;

    public function addEventDefinition(EventDefinitionInterface $eventDefinition): void;

    public function getEventDefinitionRefs(): array;
}
