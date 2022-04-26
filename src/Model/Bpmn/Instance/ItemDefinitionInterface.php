<?php

namespace Jabe\Model\Bpmn\Instance;

interface ItemDefinitionInterface extends RootElementInterface
{
    public function getStructureRef(): string;

    public function setStructureRef(string $structureRef): void;

    public function isCollection(): bool;

    public function setCollection(bool $isCollection): void;

    public function getItemKind(): string;

    public function setItemKind(string $itemKind): void;
}
