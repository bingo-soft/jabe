<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\Instance\ActivationConditionInterface;

class ComplexGatewayTest extends AbstractGatewayTest
{
    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ActivationConditionInterface::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "default")
        ];
    }

    public function testGetDefault(): void
    {
        $this->assertEquals("flow", $this->gateway->getDefault()->getId());
    }

    public function testGetActivationCondition(): void
    {
        $this->assertEquals('${test}', $this->gateway->getActivationCondition()->getTextContent());
    }
}
