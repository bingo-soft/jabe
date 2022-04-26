<?php

namespace Tests\Bpmn\Instance\Bpmndi;

use Tests\Xml\Test\AbstractTypeAssumption;
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Di\DiagramInterface;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnPlaneInterface,
    BpmnLabelStyleInterface
};

class BpmnDiagramTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::BPMNDI_NS, DiagramInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, BpmnPlaneInterface::class, 1, 1, BpmnModelConstants::BPMNDI_NS),
            new BpmnChildElementAssumption($this->model, BpmnLabelStyleInterface::class, null, null, BpmnModelConstants::BPMNDI_NS)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
