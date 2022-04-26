<?php

namespace Tests\Bpmn\Instance\Di;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use Jabe\Model\Bpmn\Instance\Dc\BoundsInterface;
use Jabe\Model\Bpmn\Instance\Di\{
    NodeInterface
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class ShapeTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true, BpmnModelConstants::DI_NS, NodeInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, BoundsInterface::class, 1, 1, BpmnModelConstants::DC_NS),
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
