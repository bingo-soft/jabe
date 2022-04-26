<?php

namespace Tests\Bpmn\Instance\Bpmndi;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Tests\Bpmn\Instance\{
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Di\LabelInterface;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelInterface
};

class BpmnLabelTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::BPMNDI_NS, LabelInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "labelStyle")
        ];
    }
}
