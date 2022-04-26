<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface InputOutputInterface extends BpmnModelElementInstanceInterface
{
    public function getInputParameters(): array;

    public function getOutputParameters(): array;
}
