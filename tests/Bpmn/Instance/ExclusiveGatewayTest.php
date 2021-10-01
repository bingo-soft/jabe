<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use BpmPlatform\Model\Bpmn\EventBasedGatewayType;
use BpmPlatform\Model\Bpmn\Instance\ActivationConditionInterface;

class ExclusiveGatewayTest extends AbstractGatewayTest
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
