<?php

namespace Tests\Bpmn\Impl\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use Tests\Bpmn\Instance\{
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};
use Jabe\Model\Bpmn\Instance\FormalExpressionInterface;

class DataPathTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;
    protected $impl = true;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, FormalExpressionInterface::class);
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
