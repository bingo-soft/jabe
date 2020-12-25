<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface CorrelationKeyInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getCorrelationProperties(): array;
}