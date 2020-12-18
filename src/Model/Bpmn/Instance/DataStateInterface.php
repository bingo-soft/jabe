<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface DataStateInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;
}
