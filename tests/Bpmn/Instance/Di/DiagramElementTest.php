<?php

namespace Tests\Bpmn\Instance\Di;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Instance\Di\ExtensionInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class DiagramElementTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true, BpmnModelConstants::DI_NS);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ExtensionInterface::class, 0, 1, BpmnModelConstants::DI_NS),
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "id", true)
        ];
    }
}
