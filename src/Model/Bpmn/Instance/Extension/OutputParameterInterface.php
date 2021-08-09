<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface OutputParameterInterface extends BpmnModelElementInstanceInterface, GenericValueElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;
}
