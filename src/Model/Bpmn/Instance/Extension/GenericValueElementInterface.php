<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface GenericValueElementInterface
{
    public function getValue(): BpmnModelElementInstanceInterface;

    public function removeValue(): void;

    public function setValue(BpmnModelElementInstanceInterface $value): void;
}
