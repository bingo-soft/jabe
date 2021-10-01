<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    SourceRef,
    TargetRef,
    Transformation
};
use BpmPlatform\Model\Bpmn\Instance\{
    AssignmentInterface,
    BaseElementInterface
};

class DataAssociationTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, SourceRef::class),
            new BpmnChildElementAssumption($this->model, TargetRef::class, 1, 1),
            new BpmnChildElementAssumption($this->model, Transformation::class, 0, 1),
            new BpmnChildElementAssumption($this->model, AssignmentInterface::class)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
