<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\EventBasedGatewayType;
use Jabe\Model\Bpmn\Instance\ActivationConditionInterface;

class InclusiveGatewayTest extends AbstractGatewayTest
{
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
}
