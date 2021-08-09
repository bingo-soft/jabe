<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface InputParameterInterface extends BpmnModelElementInstanceInterface, GenericValueElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;
}
