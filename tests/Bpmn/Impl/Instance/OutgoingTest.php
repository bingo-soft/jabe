<?php

namespace Tests\Bpmn\Impl\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use Tests\Bpmn\Instance\{
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class OutgoingTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;
    protected $impl = true;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false);
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
