<?php

namespace Jabe\Model\Bpmn\Instance;

interface RelationshipInterface extends BaseElementInterface
{
    public function getType(): string;

    public function setType(string $type): void;

    public function getDirection(): string;

    public function setDirection(string $direction): void;

    public function getSources(): array;

    public function getTargets(): array;
}
