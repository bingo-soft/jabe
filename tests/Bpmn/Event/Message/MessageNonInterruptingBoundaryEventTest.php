<?php

namespace Tests\Bpmn\Event\Message;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\DeleteJobsCmd;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface,
    CommandInvocationContext
};
use Jabe\Impl\Util\{
    ClockUtil,
    IoUtil
};
use Jabe\Test\Deployment;
use Jabe\Variable\Variables;
use Tests\Util\PluggableProcessEngineTest;

class MessageNonInterruptingBoundaryEventTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testSingleNonInterruptingBoundaryMessageEvent.bpmn20.xml"])]
    public function testSingleNonInterruptingBoundaryMessageEvent(): void
    {
        $this->runtimeService->startProcessInstanceByKey("process");

        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->count());

        $userTask = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->assertNotNull($userTask);

        $execution = $this->runtimeService->createExecutionQuery()
        ->messageEventSubscriptionName("messageName")
        ->singleResult();
        $this->assertNotNull($execution);

        // 1. case: message received before completing the task

        $this->runtimeService->messageEventReceived("messageName", $execution->getId());
        // event subscription not removed
        $execution = $this->runtimeService->createExecutionQuery()
                ->messageEventSubscriptionName("messageName")
                ->singleResult();
        $this->assertNotNull($execution);

        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $userTask = $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterMessage")->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->count());

        // send a message a second time
        $this->runtimeService->messageEventReceived("messageName", $execution->getId());
        // event subscription not removed
        $execution = $this->runtimeService->createExecutionQuery()
                ->messageEventSubscriptionName("messageName")
                ->singleResult();
        $this->assertNotNull($execution);

        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $userTask = $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterMessage")->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->count());

        // now complete the user task with the message boundary event
        $userTask = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->assertNotNull($userTask);

        $this->taskService->complete($userTask->getId());

        // event subscription removed
        $execution = $this->runtimeService->createExecutionQuery()
                ->messageEventSubscriptionName("messageName")
                ->singleResult();
        $this->assertNull($execution);

        $userTask = $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterTask")->singleResult();
        $this->assertNotNull($userTask);

        $this->taskService->complete($userTask->getId());

        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->count());

        // 2nd. case: complete the user task cancels the message subscription

        $this->runtimeService->startProcessInstanceByKey("process");

        $userTask = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->assertNotNull($userTask);
        $this->taskService->complete($userTask->getId());

        $execution = $this->runtimeService->createExecutionQuery()
        ->messageEventSubscriptionName("messageName")
        ->singleResult();
        $this->assertNull($execution);

        $userTask = $this->taskService->createTaskQuery()->taskDefinitionKey("taskAfterTask")->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterTask", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNonInterruptingEventInCombinationWithReceiveTask.bpmn20.xml"])]
    public function testNonInterruptingEventInCombinationWithReceiveTask(): void
    {
        // given
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        // when (2)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertEquals($processInstanceId, $task2Execution->getParentId());

        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNonInterruptingEventInCombinationWithReceiveTaskInConcurrentSubprocess.bpmn20.xml"])]
    public function testNonInterruptingEventInCombinationWithReceiveTaskInConcurrentSubprocess(): void
    {
        // given
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());


        // when (2)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (2)
        $this->assertEquals(3, $this->taskService->createTaskQuery()->count());
        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $afterFork = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("afterFork")
            ->singleResult();
        $this->taskService->complete($afterFork->getId());

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
        ->createExecutionQuery()
        ->activityId("task2")
        ->singleResult();

        $this->assertEquals($processInstanceId, $task2Execution->getParentId());

        $this->taskService->complete($task2->getId());
        $this->taskService->complete($task1->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNonInterruptingEventInCombinationWithReceiveTaskInsideSubProcess.bpmn20.xml"])]
    public function testNonInterruptingEventInCombinationWithReceiveTaskInsideSubProcess(): void
    {
        // given
        $instance = $this->runtimeService->startProcessInstanceByKey("process");
        $processInstanceId = $instance->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        $this->assertEquals(1, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task1Execution->getParentId());

        // when (2)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task2Execution->getParentId());

        $this->assertTrue($task1Execution->getParentId() == $task2Execution->getParentId());

        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNonInterruptingEventInCombinationWithUserTaskInsideSubProcess.bpmn20.xml"])]
    public function testNonInterruptingEventInCombinationWithUserTaskInsideSubProcess(): void
    {
        // given
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $innerTask = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("innerTask")
            ->singleResult();
        $this->assertNotNull($innerTask);

        // when (2)
        $this->taskService->complete($innerTask->getId());

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);


        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNonInterruptingEventInCombinationWithUserTask.bpmn20.xml"])]
    public function testNonInterruptingEventInCombinationWithUserTask(): void
    {
        // given
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $innerTask = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("innerTask")
            ->singleResult();
        $this->assertNotNull($innerTask);

        // when (2)
        $this->taskService->complete($innerTask->getId());

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }


    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNonInterruptingWithUserTaskAndBoundaryEvent.bpmn20.xml"])]
    public function testNonInterruptingWithUserTaskAndBoundaryEvent(): void
    {
        // given
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("innerTask")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("innerTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task2Execution->getParentId());

        // when (2)
        $this->taskService->complete($task2->getId());

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("tasks")
            ->singleResult();

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNestedEvents.bpmn20.xml"])]
    public function testNestedEvents(): void
    {
        // given
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->count());

        $innerTask = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("innerTask")
            ->singleResult();
        $this->assertNotNull($innerTask);

        $innerTaskExecution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("innerTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $innerTaskExecution->getParentId());

        // when (2)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $innerTask = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("innerTask")
            ->singleResult();
        $this->assertNotNull($innerTask);

        $innerTaskExecution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("innerTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $innerTaskExecution->getParentId());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();
        $this->assertNotNull($task1Execution);

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        // when (3)
        $this->runtimeService->correlateMessage("thirdMessage");

        // then (3)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();
        $this->assertNotNull($task1Execution);

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task2")
            ->singleResult();
        $this->assertNotNull($task2Execution);

        $this->assertEquals($processInstanceId, $task2Execution->getParentId());

        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageNonInterruptingBoundaryEventTest.testNestedEvents.bpmn20.xml"])]
    public function testNestedEventsAnotherExecutionOrder(): void
    {
        // given
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (1)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();
        $this->assertNotNull($task1Execution);

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        // when (2)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $innerTask = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("innerTask")
            ->singleResult();
        $this->assertNotNull($innerTask);

        $innerTaskExecution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("innerTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $innerTaskExecution->getParentId());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();
        $this->assertNotNull($task1Execution);

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        // when (3)
        $this->runtimeService->correlateMessage("thirdMessage");

        // then (3)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->count());

        $task1 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task1")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task1")
            ->singleResult();
        $this->assertNotNull($task1Execution);

        $this->assertEquals($processInstanceId, $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->taskDefinitionKey("task2")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->activityId("task2")
            ->singleResult();
        $this->assertNotNull($task2Execution);

        $this->assertEquals($processInstanceId, $task2Execution->getParentId());

        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }
}
