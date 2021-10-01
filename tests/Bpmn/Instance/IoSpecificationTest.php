<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption
};
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    DataInputInterface,
    DataOutputInterface,
    InputSetInterface,
    OutputSetInterface
};

class IoSpecificationTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, DataInputInterface::class),
            new BpmnChildElementAssumption($this->model, DataOutputInterface::class),
            new BpmnChildElementAssumption($this->model, InputSetInterface::class, 1),
            new BpmnChildElementAssumption($this->model, OutputSetInterface::class, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
