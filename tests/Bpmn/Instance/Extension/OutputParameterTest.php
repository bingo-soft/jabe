<?php

namespace Tests\Bpmn\Instance\Extension;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Extension\{
    InputParameterInterface,
    ScriptInterface,
    ListInterface,
    MapInterface
};
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class OutputParameterTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::EXTENSION_NS);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ScriptInterface::class, null, 1, BpmnModelConstants::EXTENSION_NS),
            new BpmnChildElementAssumption($this->model, ListInterface::class, null, 1, BpmnModelConstants::EXTENSION_NS),
            new BpmnChildElementAssumption($this->model, MapInterface::class, null, 1, BpmnModelConstants::EXTENSION_NS)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "name", false, true)
        ];
    }
}
