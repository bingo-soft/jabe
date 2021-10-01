<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    ErrorRef,
    InMessageRef,
    OutMessageRef
};
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface
};

class OperationTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, InMessageRef::class, 1, 1),
            new BpmnChildElementAssumption($this->model, OutMessageRef::class, 0, 1),
            new BpmnChildElementAssumption($this->model, ErrorRef::class)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "name", false, true),
            new AttributeAssumption(null, "implementationRef")
        ];
    }
}
