<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AbstractTypeAssumption;
use Jabe\Model\Bpmn\Instance\RootElementInterface;

class EndPointTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, RootElementInterface::class);
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
