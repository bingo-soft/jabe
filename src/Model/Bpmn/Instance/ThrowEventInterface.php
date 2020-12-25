<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ThrowEventInterface extends EventInterface
{
    public function getDataInputs(): array;

    public function getDataInputAssociations(): array;

    public function getInputSet(): InputSetInterface;

    public function setInputSet(InputSetInterface $inputSet): void;

    public function getEventDefinitions(): array;

    public function getEventDefinitionRefs(): array;
}
