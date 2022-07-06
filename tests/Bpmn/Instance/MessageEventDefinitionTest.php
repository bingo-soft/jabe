<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AttributeAssumption;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\OperationRef;
use Jabe\Model\Bpmn\Instance\MessageEventDefinitionInterface;

class MessageEventDefinitionTest extends AbstractEventDefinitionTest
{
    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, OperationRef::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "messageRef"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "class"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "delegateExpression"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "expression"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "resultVariable"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "topic"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "type"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "taskPriority")
        ];
    }

    public function testGetEventDefinition(): void
    {
        $eventDefinition = $this->eventDefinitionQuery->filterByType(
            MessageEventDefinitionInterface::class
        )->singleResult();
        $this->assertFalse($eventDefinition === null);

        $this->assertEquals("message", $eventDefinition->getMessage()->getId());
        $this->assertNull($eventDefinition->getOperation());
        $this->assertEquals("5", $eventDefinition->getTaskPriority());
    }
}
