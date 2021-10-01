<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    Incoming,
    Outgoing
};
use BpmPlatform\Model\Bpmn\Instance\{
    FlowElementInterface,
    ServiceTaskInterface,
    TaskInterface
};

class FlowNodeTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, true, null, FlowElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, Incoming::class),
            new BpmnChildElementAssumption($this->model, Outgoing::class)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "asyncAfter", false, false, false),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "asyncBefore", false, false, false),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "exclusive", false, false, true),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "jobPriority")
        ];
    }

    public function testUpdateIncomingOutgoingChildElements(): void
    {
        $modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->userTask("test")
        ->endEvent()
        ->done();

        // save current incoming and outgoing sequence flows
        $userTask = $modelInstance->getModelElementById("test");
        $incoming = $userTask->getIncoming();
        $outgoing = $userTask->getOutgoing();

        // create a new service task
        $serviceTask = $modelInstance->newInstance(ServiceTaskInterface::class);
        $serviceTask->setId("new");

        // replace the user task with the new service task
        $userTask->replaceWithElement($serviceTask);

        //assert that the new service task has the same incoming and outgoing sequence flows
        foreach ($serviceTask->getIncoming() as $task) {
            $exists = false;
            foreach ($incoming as $inner) {
                if ($task->equals($inner)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
        foreach ($serviceTask->getOutgoing() as $task) {
            $exists = false;
            foreach ($outgoing as $inner) {
                if ($task->equals($inner)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testAsyncBefore(): void
    {
        $task = $this->modelInstance->newInstance(TaskInterface::class);
        $this->assertFalse($task->isAsyncBefore());
        $task->setAsyncBefore(true);
        $this->assertTrue($task->isAsyncBefore());
    }

    public function testAsyncAfter(): void
    {
        $task = $this->modelInstance->newInstance(TaskInterface::class);
        $this->assertFalse($task->isAsyncAfter());
        $task->setAsyncAfter(true);
        $this->assertTrue($task->isAsyncAfter());
    }

    public function testExclusive(): void
    {
        $task = $this->modelInstance->newInstance(TaskInterface::class);
        $this->assertTrue($task->isExclusive());

        $task->setExclusive(false);
        $this->assertFalse($task->isExclusive());
    }

    public function testJobPriority(): void
    {
        $task = $this->modelInstance->newInstance(TaskInterface::class);
        $this->assertNull($task->getJobPriority());

        $task->setJobPriority("15");

        $this->assertEquals("15", $task->getJobPriority());
    }
}
