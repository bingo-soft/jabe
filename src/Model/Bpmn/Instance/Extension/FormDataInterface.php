<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface FormDataInterface extends BpmnModelElementInstanceInterface
{
    public function getFormFields(): array;
}
