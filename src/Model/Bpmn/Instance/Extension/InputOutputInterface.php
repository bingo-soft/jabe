<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface InputOutputInterface extends BpmnModelElementInstanceInterface
{
    public function getInputParameters(): array;

    public function getOutputParameters(): array;
}
