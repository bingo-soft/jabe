<?php

namespace Jabe\Model\Bpmn\Instance;

interface ResourceInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getResourceParameters(): array;
}
