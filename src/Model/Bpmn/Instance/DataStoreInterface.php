<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface DataStoreInterface extends RootElementInterface, ItemAwareElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getCapacity(): int;

    public function setCapacity(int $capacity): void;

    public function isUnlimited(): bool;

    public function setUnlimited(bool $isUnlimited): void;
}
