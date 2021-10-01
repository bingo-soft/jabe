<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    InterfaceRef,
    EndPointRef
};
use BpmPlatform\Model\Bpmn\Instance\{
    TaskInterface
};

class ReceiveTaskTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, TaskInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "implementation", false, false, "##WebService"),
            new AttributeAssumption(null, "instantiate", false, false, false),
            new AttributeAssumption(null, "messageRef"),
            new AttributeAssumption(null, "operationRef")
        ];
    }
}
