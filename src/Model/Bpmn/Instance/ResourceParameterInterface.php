<?php

namespace Jabe\Model\Bpmn\Instance;

interface ResourceParameterInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getType(): ItemDefinitionInterface;

    public function setType(ItemDefinitionInterface $type): void;

    public function isRequired(): bool;

    public function setRequired(bool $isRequired): void;
}
