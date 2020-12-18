<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface DataInputInterface extends ItemAwareElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function isCollection(): bool;

    public function setCollection(bool $isCollection): void;
}
