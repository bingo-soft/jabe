<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\Instance\{
    DataOutputRefs,
    OptionalOutputRefs,
    WhileExecutingOutputRefs,
    InputSetRefs
};
use Jabe\Model\Bpmn\Instance\BaseElementInterface;

class OutputSetTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, DataOutputRefs::class),
            new BpmnChildElementAssumption($this->model, OptionalOutputRefs::class),
            new BpmnChildElementAssumption($this->model, WhileExecutingOutputRefs::class),
            new BpmnChildElementAssumption($this->model, InputSetRefs::class)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "name")
        ];
    }
}
