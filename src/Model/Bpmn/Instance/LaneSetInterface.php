<?php

namespace Jabe\Model\Bpmn\Instance;

interface LaneSetInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getLanes(): array;
}
