<?php

namespace Jabe\Model\Bpmn\Instance;

interface ThrowEventInterface extends EventInterface
{
    public function getDataInputs(): array;

    public function getDataInputAssociations(): array;

    public function getInputSet(): InputSetInterface;

    public function setInputSet(InputSetInterface $inputSet): void;

    public function getEventDefinitions(): array;

    public function addEventDefinition(EventDefinitionInterface $eventDefinition): void;

    public function removeEventDefinition(EventDefinitionInterface $eventDefinition): void;

    public function getEventDefinitionRefs(): array;

    public function addEventDefinitionRef(EventDefinitionInterface $eventDefinition): void;
}
