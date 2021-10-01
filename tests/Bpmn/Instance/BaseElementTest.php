<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Instance\{
    DocumentationInterface,
    ExtensionElementsInterface,
    TaskInterface,
    StartEventInterface
};

class BaseElementTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, DocumentationInterface::class),
            new BpmnChildElementAssumption($this->model, ExtensionElementsInterface::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "id", true)
        ];
    }

    public function testId(): void
    {
        $task = $this->modelInstance->newInstance(TaskInterface::class);
        $this->assertStringStartsWith("task", $task->getId());
        $task->setId("test");
        $this->assertEquals("test", $task->getId());
        $startEvent = $this->modelInstance->newInstance(StartEventInterface::class);
        $this->assertStringStartsWith("startEvent", $startEvent->getId());
        $startEvent->setId("test");
        $this->assertEquals("test", $startEvent->getId());
    }
}
