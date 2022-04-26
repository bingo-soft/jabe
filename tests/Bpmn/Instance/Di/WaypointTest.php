<?php

namespace Tests\Bpmn\Instance\Di;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use Jabe\Model\Bpmn\Instance\Dc\PointInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class WaypointTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::DI_NS, PointInterface::class);
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
