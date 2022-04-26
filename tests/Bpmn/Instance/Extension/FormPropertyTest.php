<?php

namespace Tests\Bpmn\Instance\Extension;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Instance\Extension\{
    ValueInterface
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class FormPropertyTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::EXTENSION_NS);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ValueInterface::class, null, null, BpmnModelConstants::EXTENSION_NS)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "id"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "name"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "type"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "required", false, false, false),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "readable", false, false, true),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "writeable", false, false, true),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "variable"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "expression"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "datePattern"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "default")
        ];
    }
}
