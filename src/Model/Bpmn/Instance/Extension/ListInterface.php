<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ListInterface extends BpmnModelElementInstanceInterface
{
    public function getValues(): array;

    public function getValue(): ?ValueInterface;

    public function setValue(ValueInterface $value): void;
}
