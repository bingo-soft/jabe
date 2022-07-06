<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\Impl\Instance\{
    Source,
    Target
};
use Jabe\Model\Bpmn\Instance\{
    LinkEventDefinitionInterface
};

class LinkEventDefinitionTest extends AbstractEventDefinitionTest
{
    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, Source::class),
            new BpmnChildElementAssumption($this->model, Target::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "name", false, true)
        ];
    }

    public function testGetEventDefinition(): void
    {
        $eventDefinition = $this->eventDefinitionQuery->filterByType(
            LinkEventDefinitionInterface::class
        )->singleResult();
        $this->assertFalse($eventDefinition === null);

        $this->assertEquals("link", $eventDefinition->getName());
        $this->assertEquals("link", $eventDefinition->getSources()[0]->getName());
        $this->assertEquals("link", $eventDefinition->getTarget()->getName());
    }
}
