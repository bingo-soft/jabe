<?php

namespace Tests\Bpmn\Instance\Bpmndi;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Di\LabeledEdgeInterface;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelInterface
};

class BpmnEdgeTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::BPMNDI_NS, LabeledEdgeInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, BpmnLabelInterface::class, 0, 1, BpmnModelConstants::BPMNDI_NS)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "bpmnElement"),
            new AttributeAssumption(null, "sourceElement"),
            new AttributeAssumption(null, "targetElement"),
            new AttributeAssumption(null, "messageVisibleKind")
        ];
    }
}
