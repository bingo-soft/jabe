<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AbstractTypeAssumption;
use Jabe\Model\Bpmn\Instance\ThrowEventInterface;

class EndEventTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, ThrowEventInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
