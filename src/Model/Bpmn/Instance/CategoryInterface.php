<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface CategoryInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getCategoryValues(): array;
}
