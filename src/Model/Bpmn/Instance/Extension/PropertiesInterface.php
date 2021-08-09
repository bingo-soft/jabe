<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface PropertiesInterface extends BpmnModelElementInstanceInterface
{
    public function getProperties(): array;
}
