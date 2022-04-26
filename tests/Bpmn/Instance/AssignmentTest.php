<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AbstractTypeAssumption;
use Jabe\Model\Bpmn\Instance\BaseElementInterface;
use Jabe\Model\Bpmn\Impl\Instance\{
    From,
    To
};

class AssignmentTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, From::class, 1, 1),
            new BpmnChildElementAssumption($this->model, To::class, 1, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
