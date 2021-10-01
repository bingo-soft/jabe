<?php

namespace Tests\Bpmn\Instance\Bpmndi;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Dc\FontInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\StyleInterface;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelInterface
};

class BpmnLabelStyleTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::BPMNDI_NS, StyleInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, FontInterface::class, 1, 1, BpmnModelConstants::DC_NS)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
