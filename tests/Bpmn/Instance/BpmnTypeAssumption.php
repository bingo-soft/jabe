<?php

namespace Tests\Bpmn\Instance;

use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Xml\Test\AbstractTypeAssumption;

class BpmnTypeAssumption extends AbstractTypeAssumption
{
    public function getDefaultNamespace(): string
    {
        return BpmnModelConstants::BPMN20_NS;
    }
}
