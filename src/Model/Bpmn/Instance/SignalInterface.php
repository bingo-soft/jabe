<?php

namespace Jabe\Model\Bpmn\Instance;

interface SignalInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getStructure(): ItemDefinitionInterface;

    public function setStructure(ItemDefinitionInterface $structure): void;
}
