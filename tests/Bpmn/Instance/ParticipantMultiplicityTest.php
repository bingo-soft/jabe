<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    ErrorRef,
    InMessageRef,
    OutMessageRef
};
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface
};

class ParticipantMultiplicityTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, BaseElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "minimum", false, false, 0),
            new AttributeAssumption(null, "maximum", false, false, 1)
        ];
    }
}
