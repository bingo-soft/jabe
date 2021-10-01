<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    Source,
    Target
};
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface
};

class RelationshipTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, Source::class, 1),
            new BpmnChildElementAssumption($this->model, Target::class, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "type", false, true),
            new AttributeAssumption(null, "direction")
        ];
    }
}
