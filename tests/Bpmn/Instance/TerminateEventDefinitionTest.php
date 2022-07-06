<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\Instance\{
    TerminateEventDefinitionInterface
};

class TerminateEventDefinitionTest extends AbstractEventDefinitionTest
{
    public function testGetEventDefinition(): void
    {
        $eventDefinition = $this->eventDefinitionQuery->filterByType(
            TerminateEventDefinitionInterface::class
        )->singleResult();
        $this->assertFalse($eventDefinition === null);
    }
}
