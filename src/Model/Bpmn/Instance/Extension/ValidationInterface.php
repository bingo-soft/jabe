<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ValidationInterface extends BpmnModelElementInstanceInterface
{
    public function getConstraints(): array;
}
