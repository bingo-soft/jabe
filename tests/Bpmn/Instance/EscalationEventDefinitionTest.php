<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\Instance\{
    EscalationEventDefinitionInterface
};

class EscalationEventDefinitionTest extends AbstractEventDefinitionTest
{
    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "escalationRef")
        ];
    }

    public function testGetEventDefinition(): void
    {
        $eventDefinition = $this->eventDefinitionQuery->filterByType(
            EscalationEventDefinitionInterface::class
        )->singleResult();
        $this->assertFalse($eventDefinition === null);

        $this->assertEquals("escalation", $eventDefinition->getEscalation()->getName());
        $this->assertEquals("1337", $eventDefinition->getEscalation()->getEscalationCode());
        $this->assertEquals("itemDef", $eventDefinition->getEscalation()->getStructure()->getId());
    }
}
