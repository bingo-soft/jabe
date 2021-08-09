<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface FormDataInterface extends BpmnModelElementInstanceInterface
{
    public function getFormFields(): array;
}
