<?php

namespace Tests\Bpmn\Instance\Di;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use Jabe\Model\Bpmn\Instance\Di\EdgeInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class LabeledEdgeTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true, BpmnModelConstants::DI_NS, EdgeInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
