<?php

namespace Tests\Bpmn\Instance;

use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Xml\Test\AbstractChildElementAssumption;

class BpmnChildElementAssumption extends AbstractChildElementAssumption
{
    public function getDefaultNamespace(): string
    {
        return BpmnModelConstants::BPMN20_NS;
    }
}
