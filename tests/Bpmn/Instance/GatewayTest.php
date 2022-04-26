<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Instance\{
    FlowNodeInterface
};

class GatewayTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true, null, FlowNodeInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "gatewayDirection", false, false, "Unspecified")
        ];
    }
}
