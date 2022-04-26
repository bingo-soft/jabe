<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    RootElementInterface,
    DataStateInterface
};

class ErrorTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, RootElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "name"),
            new AttributeAssumption(null, "errorCode"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "errorMessage"),
            new AttributeAssumption(null, "structureRef")
        ];
    }
}
