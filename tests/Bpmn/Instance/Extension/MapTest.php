<?php

namespace Tests\Bpmn\Instance\Extension;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnChildElementAssumption,
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};
use Jabe\Model\Bpmn\Instance\Extension\{
    EntryInterface
};

class MapTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::EXTENSION_NS);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, EntryInterface::class, null, null, BpmnModelConstants::EXTENSION_NS)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
