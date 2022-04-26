<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ValidationInterface extends BpmnModelElementInstanceInterface
{
    public function getConstraints(): array;
}
