<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ListInterface extends BpmnModelElementInstanceInterface
{
    public function getValues(): array;
}
