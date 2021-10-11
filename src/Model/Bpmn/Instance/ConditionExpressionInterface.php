<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ConditionExpressionInterface extends FormalExpressionInterface
{
    public function getType(): string;

    public function setType(string $type): void;

    public function getResource(): ?string;

    public function setResource(string $resource): void;
}
