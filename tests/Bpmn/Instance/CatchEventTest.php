<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\Instance\EventDefinitionRef;
use Jabe\Model\Bpmn\Instance\{
    DataOutputInterface,
    DataOutputAssociationInterface,
    OutputSetInterface,
    EventDefinitionInterface,
    EventInterface
};

class CatchEventTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true, null, EventInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, DataOutputInterface::class),
            new BpmnChildElementAssumption($this->model, DataOutputAssociationInterface::class),
            new BpmnChildElementAssumption($this->model, OutputSetInterface::class, 0, 1),
            new BpmnChildElementAssumption($this->model, EventDefinitionInterface::class),
            new BpmnChildElementAssumption($this->model, EventDefinitionRef::class)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "parallelMultiple", false, false, false)
        ];
    }
}
