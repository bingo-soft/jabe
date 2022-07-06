<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    SignalEventDefinitionInterface
};

class SignalEventDefinitionTest extends AbstractEventDefinitionTest
{
    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "signalRef"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "async", false, false, false)
        ];
    }

    public function testGetEventDefinition(): void
    {
        $eventDefinition = $this->eventDefinitionQuery->filterByType(
            SignalEventDefinitionInterface::class
        )->singleResult();
        $this->assertFalse($eventDefinition === null);

        $this->assertFalse($eventDefinition->isAsync());

        $eventDefinition->setAsync(true);
        $this->assertTrue($eventDefinition->isAsync());

        $signal = $eventDefinition->getSignal();
        $this->assertFalse($signal === null);
        $this->assertEquals("signal", $signal->getId());
        $this->assertEquals("signal", $signal->getName());
        $this->assertEquals("itemDef", $signal->getStructure()->getId());
    }
}
