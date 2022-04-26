<?php

namespace Jabe\Model\Bpmn\Instance;

interface MessageInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getItem(): ItemDefinitionInterface;

    public function setItem(ItemDefinitionInterface $item): void;
}
