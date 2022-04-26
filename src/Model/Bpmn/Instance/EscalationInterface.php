<?php

namespace Jabe\Model\Bpmn\Instance;

interface EscalationInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getEscalationCode(): ?string;

    public function setEscalationCode(string $escalationCode): void;

    public function getStructure(): ItemDefinitionInterface;

    public function setStructure(ItemDefinitionInterface $structure): void;
}
