<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Instance\{
    DataInputAssociationInterface,
    DataOutputAssociationInterface,
    FlowNodeInterface,
    IoSpecificationInterface,
    PropertyInterface,
    ResourceRoleInterface,
    LoopCharacteristicsInterface
};

class ActivityTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true, null, FlowNodeInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, IoSpecificationInterface::class, 0, 1),
            new BpmnChildElementAssumption($this->model, PropertyInterface::class),
            new BpmnChildElementAssumption($this->model, DataInputAssociationInterface::class),
            new BpmnChildElementAssumption($this->model, DataOutputAssociationInterface::class),
            new BpmnChildElementAssumption($this->model, ResourceRoleInterface::class),
            new BpmnChildElementAssumption($this->model, LoopCharacteristicsInterface::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "isForCompensation", false, false, false),
            new AttributeAssumption(null, "startQuantity", false, false, 1),
            new AttributeAssumption(null, "completionQuantity", false, false, 1),
            new AttributeAssumption(null, "default")
        ];
    }
}
