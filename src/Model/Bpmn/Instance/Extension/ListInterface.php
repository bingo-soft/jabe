<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ListInterface extends BpmnModelElementInstanceInterface
{
    public function getValues(): array;

    public function addValue(ValueInterface $value): void;

    public function removeValue(ValueInterface $value): void;

    public function clearValues(): void;

    public function getValue(): ?ValueInterface;

    public function setValue(ValueInterface $value): void;
}
