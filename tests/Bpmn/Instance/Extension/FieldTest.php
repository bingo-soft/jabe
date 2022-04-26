<?php

namespace Tests\Bpmn\Instance\Extension;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Instance\Extension\{
    ExpressionInterface,
    StringInterface
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class FieldTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::EXTENSION_NS);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ExpressionInterface::class, 0, 1, BpmnModelConstants::EXTENSION_NS),
            new BpmnChildElementAssumption($this->model, StringInterface::class, 0, 1, BpmnModelConstants::EXTENSION_NS)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "name"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "expression"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "stringValue")
        ];
    }
}
