<?php

namespace Tests\Bpmn\Event\Message;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\EventSubscriptionQueryImpl;
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
use Tests\Util\{
    PluggableProcessEngineTest,
    TestExecutionListener
};

class MessageEventSubprocessTest extends PluggableProcessEngineTest
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

        TestExecutionListener::reset();
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testTwoInterruptingUnderProcessDefinition.bpmn20.xml"])]
    public function testTwoInterruptingUnderProcessDefinition(): void
    {
        $this->testInterruptingUnderProcessDefinition(2);
    }

    private function createEventSubscriptionQuery()
    {
        return new EventSubscriptionQueryImpl($this->processEngineConfiguration->getCommandExecutorTxRequired());
    }

    private function testInterruptingUnderProcessDefinition(int $expectedNumberOfEventSubscriptions = 1): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // the process instance must have a message event subscription:
        $execution = $this->runtimeService->createExecutionQuery()
            ->executionId($processInstance->getId())
            ->messageEventSubscriptionName("newMessage")
            ->singleResult();
        $this->assertNotNull($execution);
        $this->assertEquals($expectedNumberOfEventSubscriptions, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(1, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // if we trigger the usertask, the process terminates and the event subscription is removed:
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("task", $task->getTaskDefinitionKey());
        $this->taskService->complete($task->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // now we start a new instance but this time we trigger the event subprocess:
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->messageEventReceived("newMessage", $processInstance->getId());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("eventSubProcessTask", $task->getTaskDefinitionKey());
        $this->taskService->complete($task->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testEventSubprocessListenersInvoked.bpmn"])]
    public function testEventSubprocessListenersInvoked(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        $this->runtimeService->correlateMessage("message");

        $taskInEventSubProcess = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("taskInEventSubProcess", $taskInEventSubProcess->getTaskDefinitionKey());

        $this->taskService->complete($taskInEventSubProcess->getId());

        $collectedEvents = TestExecutionListener::$collectedEvents;

        $this->assertEquals("taskInMainFlow-start", $collectedEvents[0]);
        $this->assertEquals("taskInMainFlow-end", $collectedEvents[1]);
        $this->assertEquals("eventSubProcess-start", $collectedEvents[2]);
        $this->assertEquals("startEventInSubProcess-start", $collectedEvents[3]);
        $this->assertEquals("startEventInSubProcess-end", $collectedEvents[4]);
        $this->assertEquals("taskInEventSubProcess-start", $collectedEvents[5]);
        $this->assertEquals("taskInEventSubProcess-end", $collectedEvents[6]);
        $this->assertEquals("eventSubProcess-end", $collectedEvents[7]);

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() > ProcessEngineConfigurationImpl::HISTORYLEVEL_NONE) {
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInMainFlow")->canceled()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("startEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInEventSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("endEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("eventSubProcess")->finished()->count());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingEventSubprocessListenersInvoked.bpmn"])]
    public function testNonInterruptingEventSubprocessListenersInvoked(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        $this->runtimeService->correlateMessage("message");

        $taskInMainFlow = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("taskInMainFlow")->singleResult();
        $this->assertNotNull($taskInMainFlow);

        $taskInEventSubProcess = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("taskInEventSubProcess")->singleResult();
        $this->assertNotNull($taskInEventSubProcess);

        $this->taskService->complete($taskInMainFlow->getId());
        $this->taskService->complete($taskInEventSubProcess->getId());

        $collectedEvents = TestExecutionListener::$collectedEvents;

        $this->assertEquals("taskInMainFlow-start", $collectedEvents[0]);
        $this->assertEquals("eventSubProcess-start", $collectedEvents[1]);
        $this->assertEquals("startEventInSubProcess-start", $collectedEvents[2]);
        $this->assertEquals("startEventInSubProcess-end", $collectedEvents[3]);
        $this->assertEquals("taskInEventSubProcess-start", $collectedEvents[4]);
        $this->assertEquals("taskInMainFlow-end", $collectedEvents[5]);
        $this->assertEquals("taskInEventSubProcess-end", $collectedEvents[6]);
        $this->assertEquals("eventSubProcess-end", $collectedEvents[7]);

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() > ProcessEngineConfigurationImpl::HISTORYLEVEL_NONE) {
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("startEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInMainFlow")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInEventSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("endEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("eventSubProcess")->finished()->count());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNestedEventSubprocessListenersInvoked.bpmn"])]
    public function testNestedEventSubprocessListenersInvoked(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        $this->runtimeService->correlateMessage("message");

        $taskInEventSubProcess = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("taskInEventSubProcess", $taskInEventSubProcess->getTaskDefinitionKey());

        $this->taskService->complete($taskInEventSubProcess->getId());

        $collectedEvents = TestExecutionListener::$collectedEvents;

        $this->assertEquals("taskInMainFlow-start", $collectedEvents[0]);
        $this->assertEquals("taskInMainFlow-end", $collectedEvents[1]);
        $this->assertEquals("eventSubProcess-start", $collectedEvents[2]);
        $this->assertEquals("startEventInSubProcess-start", $collectedEvents[3]);
        $this->assertEquals("startEventInSubProcess-end", $collectedEvents[4]);
        $this->assertEquals("taskInEventSubProcess-start", $collectedEvents[5]);
        $this->assertEquals("taskInEventSubProcess-end", $collectedEvents[6]);
        $this->assertEquals("eventSubProcess-end", $collectedEvents[7]);

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() > ProcessEngineConfigurationImpl::HISTORYLEVEL_NONE) {
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInMainFlow")->canceled()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("startEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInEventSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("endEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("eventSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("subProcess")->finished()->count());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNestedNonInterruptingEventSubprocessListenersInvoked.bpmn"])]
    public function testNestedNonInterruptingEventSubprocessListenersInvoked(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        $this->runtimeService->correlateMessage("message");

        $taskInMainFlow = $this->taskService->createTaskQuery()->taskDefinitionKey("taskInMainFlow")->singleResult();
        $this->assertNotNull($taskInMainFlow);

        $taskInEventSubProcess = $this->taskService->createTaskQuery()->taskDefinitionKey("taskInEventSubProcess")->singleResult();
        $this->assertNotNull($taskInEventSubProcess);

        $this->taskService->complete($taskInMainFlow->getId());
        $this->taskService->complete($taskInEventSubProcess->getId());

        $collectedEvents = TestExecutionListener::$collectedEvents;

        $this->assertEquals("taskInMainFlow-start", $collectedEvents[0]);
        $this->assertEquals("eventSubProcess-start", $collectedEvents[1]);
        $this->assertEquals("startEventInSubProcess-start", $collectedEvents[2]);
        $this->assertEquals("startEventInSubProcess-end", $collectedEvents[3]);
        $this->assertEquals("taskInEventSubProcess-start", $collectedEvents[4]);
        $this->assertEquals("taskInMainFlow-end", $collectedEvents[5]);
        $this->assertEquals("taskInEventSubProcess-end", $collectedEvents[6]);
        $this->assertEquals("eventSubProcess-end", $collectedEvents[7]);

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() > ProcessEngineConfigurationImpl::HISTORYLEVEL_NONE) {
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInMainFlow")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("startEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInEventSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("endEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("eventSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("subProcess")->finished()->count());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testEventSubprocessBoundaryListenersInvoked.bpmn"])]
    public function testEventSubprocessBoundaryListenersInvoked(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        $this->runtimeService->correlateMessage("message");

        $taskInEventSubProcess = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("taskInEventSubProcess", $taskInEventSubProcess->getTaskDefinitionKey());

        $this->runtimeService->correlateMessage("message2");

        $collectedEvents = TestExecutionListener::$collectedEvents;

        $this->assertEquals("taskInMainFlow-start", $collectedEvents[0]);
        $this->assertEquals("taskInMainFlow-end", $collectedEvents[1]);
        $this->assertEquals("eventSubProcess-start", $collectedEvents[2]);
        $this->assertEquals("startEventInSubProcess-start", $collectedEvents[3]);
        $this->assertEquals("startEventInSubProcess-end", $collectedEvents[4]);
        $this->assertEquals("taskInEventSubProcess-start", $collectedEvents[5]);
        $this->assertEquals("taskInEventSubProcess-end", $collectedEvents[6]);
        $this->assertEquals("eventSubProcess-end", $collectedEvents[7]);

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() > ProcessEngineConfigurationImpl::HISTORYLEVEL_NONE) {
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInMainFlow")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInMainFlow")->canceled()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("startEventInSubProcess")->finished()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("taskInEventSubProcess")->canceled()->count());
            $this->assertEquals(1, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("eventSubProcess")->finished()->count());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingUnderProcessDefinition.bpmn20.xml"])]
    public function testNonInterruptingUnderProcessDefinition(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // the process instance must have a message event subscription:
        $execution = $this->runtimeService->createExecutionQuery()
            ->executionId($processInstance->getId())
            ->messageEventSubscriptionName("newMessage")
            ->singleResult();
        $this->assertNotNull($execution);
        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(1, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // if we trigger the usertask, the process terminates and the event subscription is removed:
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("task", $task->getTaskDefinitionKey());
        $this->taskService->complete($task->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // ###################### now we start a new instance but this time we trigger the event subprocess:
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->messageEventReceived("newMessage", $processInstance->getId());

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        // now let's first complete the task in the main flow:
        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->taskService->complete($task->getId());
        // we still have 2 executions (one for process instance, one for event subprocess):
        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // now let's complete the task in the event subprocess
        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->singleResult();
        $this->taskService->complete($task->getId());
        // done!
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // #################### again, the other way around:

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->messageEventReceived("newMessage", $processInstance->getId());

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->singleResult();
        $this->taskService->complete($task->getId());
        // we still have 1 execution:
        $this->assertEquals(1, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->taskService->complete($task->getId());
        // done!
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingUnderProcessDefinitionScope.bpmn20.xml"])]
    public function testNonInterruptingUnderProcessDefinitionScope(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // the process instance must have a message event subscription:
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("newMessage")
            ->singleResult();
        $this->assertNotNull($execution);
        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // if we trigger the usertask, the process terminates and the event subscription is removed:
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("task", $task->getTaskDefinitionKey());
        $this->taskService->complete($task->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // ###################### now we start a new instance but this time we trigger the event subprocess:
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->correlateMessage("newMessage");

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // now let's first complete the task in the main flow:
        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->taskService->complete($task->getId());
        // we still have 2 executions (one for process instance, one for subprocess scope):
        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // now let's complete the task in the event subprocess
        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->singleResult();
        $this->taskService->complete($task->getId());
        // done!
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // #################### again, the other way around:

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->correlateMessage("newMessage");

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->singleResult();
        $this->taskService->complete($task->getId());
        // we still have 2 executions (usertask in main flow is scope):
        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->taskService->complete($task->getId());
        // done!
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingInEmbeddedSubprocess.bpmn20.xml"])]
    public function testNonInterruptingInEmbeddedSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // the process instance must have a message event subscription:
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("newMessage")
            ->singleResult();
        $this->assertNotNull($execution);
        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // if we trigger the usertask, the process terminates and the event subscription is removed:
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("task", $task->getTaskDefinitionKey());
        $this->taskService->complete($task->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // ###################### now we start a new instance but this time we trigger the event subprocess:
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->correlateMessage("newMessage");

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        // now let's first complete the task in the main flow:
        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->taskService->complete($task->getId());
        // we still have 3 executions:
        $this->assertEquals(3, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // now let's complete the task in the event subprocess
        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->singleResult();
        $this->taskService->complete($task->getId());
        // done!
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // #################### again, the other way around:

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->correlateMessage("newMessage");

        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->singleResult();
        $this->taskService->complete($task->getId());
        // we still have 2 executions:
        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("task")->singleResult();
        $this->taskService->complete($task->getId());
        // done!
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertEquals(0, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testMultipleNonInterruptingInEmbeddedSubprocess.bpmn20.xml"])]
    public function testMultipleNonInterruptingInEmbeddedSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // the process instance must have a message event subscription:
        $subProcess = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("newMessage")
            ->singleResult();
        $this->assertNotNull($subProcess);
        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        $subProcessTask = $this->taskService->createTaskQuery()->taskDefinitionKey("subProcessTask")->singleResult();
        $this->assertNotNull($subProcessTask);

        // start event sub process multiple times
        for ($i = 1; $i < 3; $i++) {
            $this->runtimeService->messageEventReceived("newMessage", $subProcess->getId());

            // check that now i event sub process tasks exist
            $eventSubProcessTasks = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->list();
            $this->assertEquals($i, count($eventSubProcessTasks));
        }

        // complete sub process task
        $this->taskService->complete($subProcessTask->getId());

        // after complete the sub process task all task should be deleted because of the terminating end event
        $this->assertEquals(0, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        // and the process instance should be ended
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingInMultiParallelEmbeddedSubprocess.bpmn20.xml"])]
    public function testNonInterruptingInMultiParallelEmbeddedSubprocess(): void
    {
        // #################### I. start process and only complete the tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // assert execution tree: scope (process) > scope (subprocess) > 2 x subprocess + usertask
        $this->assertEquals(6, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // expect: two subscriptions, one for each instance
        $this->assertEquals(2, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // expect: two subprocess instances, i.e. two tasks created
        $tasks = $this->taskService->createTaskQuery()->list();
        // then: complete both tasks
        foreach ($tasks as $task) {
            $this->assertEquals("subUserTask", $task->getTaskDefinitionKey());
            $this->taskService->complete($task->getId());
        }

        // expect: the event subscriptions are removed
        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // then: complete the last task of the main process
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult()->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());

        // #################### II. start process and correlate messages to trigger subprocesses instantiation
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        foreach ($this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->list() as $es) {
            $this->runtimeService->messageEventReceived("message", $es->getExecutionId()); // trigger
        }

        // expect: both subscriptions are remaining and they can be re-triggered as long as the subprocesses are active
        $this->assertEquals(2, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // expect: two additional task, one for each triggered process
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Message User Task")->list();
        $this->assertEquals(2, count($tasks));
        foreach ($tasks as $task) { // complete both tasks
            $this->taskService->complete($task->getId());
        }

        // then: complete one subprocess
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Sub User Task")->list()[0]->getId());

        // expect: only the subscription of the second subprocess instance is left
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // then: trigger the second subprocess again
        $this->runtimeService->messageEventReceived(
            "message",
            $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->singleResult()->getExecutionId()
        );

        // expect: one message subprocess task exist
        $this->assertEquals(1, count($this->taskService->createTaskQuery()->taskName("Message User Task")->list()));

        // then: complete all inner subprocess tasks
        $tasks = $this->taskService->createTaskQuery()->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }

        // expect: no subscription is left
        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // then: complete the last task of the main process
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult()->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingInMultiSequentialEmbeddedSubprocess.bpmn20.xml"])]
    public function testNonInterruptingInMultiSequentialEmbeddedSubprocess(): void
    {
        // start process and trigger the first message sub process
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->messageEventReceived("message", $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->singleResult()->getExecutionId());

        // expect: one subscription is remaining for the first instance
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // then: complete both tasks (subprocess and message subprocess)
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Message User Task")->singleResult()->getId());
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Sub User Task")->list()[0]->getId());

        // expect: the second instance is started
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // then: just complete this
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Sub User Task")->list()[0]->getId());

        // expect: no subscription is left
        $this->assertEquals(0, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());

        // then: complete the last task of the main process
        $this->taskService->complete($this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult()->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingWithParallelForkInsideEmbeddedSubProcess.bpmn20.xml"])]
    public function testNonInterruptingWithParallelForkInsideEmbeddedSubProcess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->runtimeService->messageEventReceived("newMessage", $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->singleResult()->getExecutionId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();

        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }

        $this->testRule->assertProcessEnded($processInstance->getId());

        $this->assertTrue(true);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingWithReceiveTask.bpmn20.xml"])]
    public function testNonInterruptingWithReceiveTask(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());

        $task1 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("eventSubProcessTask")
            ->singleResult();
        $this->assertNotNull($task1);

        // when (2)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());

        $task1 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("eventSubProcessTask")
            ->singleResult();
        $this->assertNotNull($task1);

        $task2 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("userTask")
            ->singleResult();
        $this->assertNotNull($task2);

        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstanceId)->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingWithAsyncConcurrentTask.bpmn20.xml"])]
    public function testNonInterruptingWithAsyncConcurrentTask(): void
    {
        // given a process instance with an asyncBefore user task
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // and a triggered non-interrupting subprocess with a user task
        $this->runtimeService->correlateMessage("message");

        // then triggering the async job should be successful
        $asyncJob = $this->managementService->createJobQuery()->processInstanceId($processInstanceId)->singleResult();
        $this->assertNotNull($asyncJob);
        $this->managementService->executeJob($asyncJob->getId());

        // and there should be two tasks now that can be completed successfully
        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());
        $processTask = $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->taskDefinitionKey("userTask")->singleResult();
        $eventSubprocessTask = $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->taskDefinitionKey("eventSubProcessTask")->singleResult();

        $this->assertNotNull($processTask);
        $this->assertNotNull($eventSubprocessTask);

        $this->taskService->complete($processTask->getId());
        $this->taskService->complete($eventSubprocessTask->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingWithReceiveTaskInsideEmbeddedSubProcess.bpmn20.xml"])]
    public function testNonInterruptingWithReceiveTaskInsideEmbeddedSubProcess(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());

        $task1 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("eventSubProcessTask")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->processInstanceId($processInstanceId)
            ->activityId("eventSubProcessTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task1Execution->getParentId());

        // when (2)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());

        $task1 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("eventSubProcessTask")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->processInstanceId($processInstanceId)
            ->activityId("eventSubProcessTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("userTask")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->processInstanceId($processInstanceId)
            ->activityId("eventSubProcessTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task2Execution->getParentId());

        // both have the same parent (but it is not the process instance)
        $this->assertTrue($task1Execution->getParentId() == $task2Execution->getParentId());

        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstanceId)->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingWithUserTaskAndBoundaryEventInsideEmbeddedSubProcess.bpmn20.xml"])]
    public function testNonInterruptingWithUserTaskAndBoundaryEventInsideEmbeddedSubProcess(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when
        $this->runtimeService->correlateMessage("newMessage");

        // then
        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());

        $task1 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("eventSubProcessTask")
            ->singleResult();
        $this->assertNotNull($task1);

        $task1Execution = $this->runtimeService
            ->createExecutionQuery()
            ->processInstanceId($processInstanceId)
            ->activityId("eventSubProcessTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task1Execution->getParentId());

        $task2 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("task")
            ->singleResult();
        $this->assertNotNull($task2);

        $task2Execution = $this->runtimeService
            ->createExecutionQuery()
            ->processInstanceId($processInstanceId)
            ->activityId("eventSubProcessTask")
            ->singleResult();

        $this->assertFalse($processInstanceId == $task2Execution->getParentId());

        // both have the same parent (but it is not the process instance)
        $this->assertTrue($task1Execution->getParentId() == $task2Execution->getParentId());

        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstanceId)->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingOutsideEmbeddedSubProcessWithReceiveTaskInsideEmbeddedSubProcess.bpmn20.xml"])]
    public function testNonInterruptingOutsideEmbeddedSubProcessWithReceiveTaskInsideEmbeddedSubProcess(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("process")->getId();

        // when (1)
        $this->runtimeService->correlateMessage("firstMessage");

        // then (1)
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());

        $task1 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("eventSubProcessTask")
            ->singleResult();
        $this->assertNotNull($task1);

        // when (2)
        $this->runtimeService->correlateMessage("secondMessage");

        // then (2)
        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());

        $task1 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("eventSubProcessTask")
            ->singleResult();
        $this->assertNotNull($task1);

        $task2 = $this->taskService->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->taskDefinitionKey("userTask")
            ->singleResult();
        $this->assertNotNull($task2);

        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstanceId)->count());

        $this->taskService->complete($task1->getId());
        $this->taskService->complete($task2->getId());

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testNonInterruptingWithTerminatingEndEvent.bpmn20.xml"])]
    public function testNonInterruptingWithTerminatingEndEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("Inner User Task", $task->getName());
        $this->runtimeService->correlateMessage("message");

        $eventSubprocessTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Event User Task")->singleResult();
        $this->assertFalse($eventSubprocessTask == null);
        $this->taskService->complete($eventSubprocessTask->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageEventSubprocessTest.testExpressionInMessageNameInInterruptingSubProcessDefinition.bpmn20.xml"])]
    public function testExpressionInMessageNameInInterruptingSubProcessDefinition(): void
    {
        // given an process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // when receiving the message
        $this->runtimeService->messageEventReceived("newMessage-foo", $processInstance->getId());

        // the the subprocess is triggered and we can complete the task
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("eventSubProcessTask", $task->getTaskDefinitionKey());
        $this->taskService->complete($task->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }
}
