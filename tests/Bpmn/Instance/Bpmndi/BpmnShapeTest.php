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
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Di\LabeledShapeInterface;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelInterface
};

class BpmnShapeTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::BPMNDI_NS, LabeledShapeInterface::class);
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
            new AttributeAssumption(null, "isHorizontal"),
            new AttributeAssumption(null, "isExpanded"),
            new AttributeAssumption(null, "isMarkerVisible"),
            new AttributeAssumption(null, "isMessageVisible"),
            new AttributeAssumption(null, "participantBandKind"),
            new AttributeAssumption(null, "choreographyActivityShape")
        ];
    }
}
