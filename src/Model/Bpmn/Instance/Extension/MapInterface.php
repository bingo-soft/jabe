<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface MapInterface extends BpmnModelElementInstanceInterface
{
    public function getEntries(): array;
}
