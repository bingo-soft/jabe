<?php

namespace Jabe\Model\Bpmn\Instance;

interface PropertyInterface extends ItemAwareElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;
}
