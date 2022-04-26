<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\EventBasedGatewayType;
use Jabe\Model\Bpmn\Instance\ActivationConditionInterface;

class EventBasedGatewayTest extends AbstractGatewayTest
{
    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "instantiate", false, false, false),
            new AttributeAssumption(null, "eventGatewayType", false, false, EventBasedGatewayType::EXCLUSIVE)
        ];
    }

    public function testGetInstantiate(): void
    {
        $this->assertTrue($this->gateway->isInstantiate());
    }

    public function getEventGatewayType(): void
    {
        $this->assertEquals(EventBasedGatewayType::PARALLEL, $this->gateway->getEventGatewayType());
    }
}
