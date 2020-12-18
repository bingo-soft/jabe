<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface LinkEventDefinitionInterface extends EventDefinitionInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getSources(): array;

    public function getTarget(): LinkEventDefinitionInterface;

    public function setTarget(LinkEventDefinitionInterface $target): void;
}
