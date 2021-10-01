<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    PartitionElement,
    FlowNodeRef,
    ChildLaneSet
};
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface
};

class LaneTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, PartitionElement::class, 0, 1),
            new BpmnChildElementAssumption($this->model, FlowNodeRef::class),
            new BpmnChildElementAssumption($this->model, ChildLaneSet::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "name"),
            new AttributeAssumption(null, "partitionElementRef")
        ];
    }
}
