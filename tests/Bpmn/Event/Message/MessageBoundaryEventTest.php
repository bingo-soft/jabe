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

class MessageBoundaryEventTest extends PluggableProcessEngineTest
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

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testSingleBoundaryMessageEvent.bpmn20.xml"])]
    public function testSingleBoundaryMessageEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);

        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->processInstanceId($processInstance->getId())
            ->singleResult();
        $this->assertNotNull($execution);

        // 1. case: message received cancels the task

        $this->runtimeService->messageEventReceived("messageName", $execution->getId());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        // 2nd. case: complete the user task cancels the message subscription

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->taskService->complete($userTask->getId());

        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->processInstanceId($processInstance->getId())
            ->singleResult();
        $this->assertNull($execution);

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterTask", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    public function testDoubleBoundaryMessageEventSameMessageId(): void
    {
        // deployment fails when two boundary message events have the same messageId
        $this->expectException(\Exception::class);
        $this->repositoryService
            ->createDeployment()
            ->addClasspathResource("tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testDoubleBoundaryMessageEventSameMessageId.bpmn20.xml")
            ->deploy();
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testDoubleBoundaryMessageEvent.bpmn20.xml"])]
    public function testDoubleBoundaryMessageEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);

        // the executions for both messageEventSubscriptionNames are the same
        $execution1 = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())
            ->messageEventSubscriptionName("messageName_1")
            ->processInstanceId($processInstance->getId())
            ->singleResult();
        $this->assertNotNull($execution1);

        $execution2 = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_2")
            //->processInstanceId($processInstance->getId())
            ->singleResult();
        $this->assertNotNull($execution2);

        $this->assertEquals($execution1->getId(), $execution2->getId());

        ///////////////////////////////////////////////////////////////////////////////////
        // 1. first message received cancels the task and the execution and both subscriptions
        $this->runtimeService->messageEventReceived("messageName_1", $execution1->getId());

        // this should then throw an exception because execution2 no longer exists
        try {
            $this->runtimeService->messageEventReceived("messageName_2", $execution2->getId());
        } catch (\Exception $e) {
            //
        }

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage_1", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        /////////////////////////////////////////////////////////////////////
        // 2. complete the user task cancels the message subscriptions

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->taskService->complete($userTask->getId());

        $execution1 = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_1")
            ->processInstanceId($processInstance->getId())
            ->singleResult();
        $this->assertNull($execution1);
        $execution2 = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_2")
            ->processInstanceId($processInstance->getId())
            ->singleResult();
        $this->assertNull($execution2);

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterTask", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testDoubleBoundaryMessageEventMultiInstance.bpmn20.xml"])]
    public function testDoubleBoundaryMessageEventMultiInstance(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        // assume we have 7 executions
        // one process instance
        // one execution for scope created for boundary message event
        // five execution because we have loop cardinality 5
        $this->assertEquals(7, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $this->assertEquals(5, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $execution1 = $this->runtimeService->createExecutionQuery()->messageEventSubscriptionName("messageName_1")->singleResult();
        $execution2 = $this->runtimeService->createExecutionQuery()->messageEventSubscriptionName("messageName_2")->singleResult();
        // both executions are the same
        $this->assertEquals($execution1->getId(), $execution2->getId());

        ///////////////////////////////////////////////////////////////////////////////////
        // 1. first message received cancels all tasks and the executions and both subscriptions
        $this->runtimeService->messageEventReceived("messageName_1", $execution1->getId());

        // this should then throw an exception because execution2 no longer exists
        try {
            $this->runtimeService->messageEventReceived("messageName_2", $execution2->getId());
        } catch (\Exception $e) {
            //
        }

        // only process instance left
        $this->assertEquals(1, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage_1", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());


        ///////////////////////////////////////////////////////////////////////////////////
        // 2. complete the user task cancels the message subscriptions

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        // assume we have 7 executions
        // one process instance
        // one execution for scope created for boundary message event
        // five execution because we have loop cardinality 5
        $this->assertEquals(7, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $this->assertEquals(5, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $execution1 = $this->runtimeService->createExecutionQuery()->messageEventSubscriptionName("messageName_1")->singleResult();
        $execution2 = $this->runtimeService->createExecutionQuery()->messageEventSubscriptionName("messageName_2")->singleResult();
        // both executions are the same
        $this->assertEquals($execution1->getId(), $execution2->getId());

        $userTasks = $this->taskService->createTaskQuery()->list();
        $this->assertNotNull($userTasks);
        $this->assertEquals(5, count($userTasks));

        // as long as tasks exists, the message subscriptions exist
        for ($i = 0; $i < count($userTasks) - 1; $i += 1) {
            $task = $userTasks[$i];
            $this->taskService->complete($task->getId());

            $execution1 = $this->runtimeService->createExecutionQuery()
                ->messageEventSubscriptionName("messageName_1")
                ->singleResult();
            $this->assertNotNull($execution1);
            $execution2 = $this->runtimeService->createExecutionQuery()
                ->messageEventSubscriptionName("messageName_2")
                ->singleResult();
            $this->assertNotNull($execution2);
        }

        // only one task left
        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->taskService->complete($userTask->getId());

        // after last task is completed, no message subscriptions left
        $execution1 = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_1")
            ->singleResult();
        $this->assertNull($execution1);
        $execution2 = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_2")
            ->singleResult();
        $this->assertNull($execution2);

        // complete last task to end process
        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterTask", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testBoundaryMessageEventInsideSubprocess.bpmn20.xml"])]
    public function testBoundaryMessageEventInsideSubprocess(): void
    {
        // this time the boundary events are placed on a user task that is contained inside a sub process

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->assertEquals(3, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);

        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->singleResult();
        $this->assertNotNull($execution);

        ///////////////////////////////////////////////////
        // 1. case: message received cancels the task

        $this->runtimeService->messageEventReceived("messageName", $execution->getId());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        ///////////////////////////////////////////////////
        // 2nd. case: complete the user task cancels the message subscription

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->taskService->complete($userTask->getId());

        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->singleResult();
        $this->assertNull($execution);

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterTask", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testBoundaryMessageEventOnSubprocessAndInsideSubprocess.bpmn20.xml"])]
    public function testBoundaryMessageEventOnSubprocessAndInsideSubprocess(): void
    {
        // this time the boundary events are placed on a user task that is contained inside a sub process
        // and on the subprocess itself

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->assertEquals(3, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);

        $execution1 = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->singleResult();
        $this->assertNotNull($execution1);

        $execution2 = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->singleResult();
        $this->assertNotNull($execution2);

        $this->assertNotSame($execution1->getId(), $execution2->getId());

        /////////////////////////////////////////////////////////////
        // first case: we complete the inner $userTask->

        $this->taskService->complete($userTask->getId());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterTask", $userTask->getTaskDefinitionKey());

        // the inner subscription is cancelled
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->singleResult();
        $this->assertNull($execution);

        // the outer subscription still exists
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->singleResult();
        $this->assertNotNull($execution);

        // now complete the second usertask
        $this->taskService->complete($userTask->getId());

        // now the outer event subscription is cancelled as well
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->singleResult();
        $this->assertNull($execution);

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterSubprocess", $userTask->getTaskDefinitionKey());

        // now complete the outer usertask
        $this->taskService->complete($userTask->getId());

        /////////////////////////////////////////////////////////////
        // second case: we signal the inner message event

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->singleResult();
        $this->runtimeService->messageEventReceived("messageName", $execution->getId());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage", $userTask->getTaskDefinitionKey());

        // the inner subscription is removed
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->singleResult();
        $this->assertNull($execution);

        // the outer subscription still exists
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->singleResult();
        $this->assertNotNull($execution);

        // now complete the second usertask
        $this->taskService->complete($userTask->getId());

        // now the outer event subscription is cancelled as well
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->singleResult();
        $this->assertNull($execution);

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterSubprocess", $userTask->getTaskDefinitionKey());

        // now complete the outer usertask
        $this->taskService->complete($userTask->getId());

        /////////////////////////////////////////////////////////////
        // third case: we signal the outer message event

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->singleResult();
        $this->runtimeService->messageEventReceived("messageName2", $execution->getId());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterOuterMessageBoundary", $userTask->getTaskDefinitionKey());

        // the inner subscription is removed
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->singleResult();
        $this->assertNull($execution);

        // the outer subscription is removed
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->singleResult();
        $this->assertNull($execution);

        // now complete the second usertask
        $this->taskService->complete($userTask->getId());
        // and we are done
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testBoundaryMessageEventOnSubprocess.bpmn20.xml"])]
    public function testBoundaryMessageEventOnSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->assertEquals(2, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);

        // 1. case: message one received cancels the task

        $executionMessageOne = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_one")
            ->singleResult();
        $this->assertNotNull($executionMessageOne);

        $this->runtimeService->messageEventReceived("messageName_one", $executionMessageOne->getId());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage_one", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        // 2nd. case: message two received cancels the task

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $executionMessageTwo = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_two")
            ->singleResult();
        $this->assertNotNull($executionMessageTwo);

        $this->runtimeService->messageEventReceived("messageName_two", $executionMessageTwo->getId());

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage_two", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        // 3rd. case: complete the user task cancels the message subscription

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->taskService->complete($userTask->getId());

        $executionMessageOne = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_one")
            ->singleResult();
        $this->assertNull($executionMessageOne);

        $executionMessageTwo = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName_two")
            ->singleResult();
        $this->assertNull($executionMessageTwo);

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterSubProcess", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testBoundaryMessageEventOnSubprocessWithIntermediateMessageCatch.bpmn"])]
    public function testBoundaryMessageEventOnSubprocessWithIntermediateMessageCatch(): void
    {
        // given
        // a process instance waiting inside the intermediate message catch inside the subprocess
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // when
        // I cancel the subprocess
        $this->runtimeService->correlateMessage("cancelMessage");

        // then
        // the process instance is ended
        $this->testRule->assertProcessEnded($processInstance->getId());

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() > ProcessEngineConfigurationImpl::HISTORYLEVEL_NONE) {
            // and all activity instances in history have an end time set
            $hais = $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->list();
            foreach ($hais as $historicActivityInstance) {
                $this->assertNotNull($historicActivityInstance->getEndTime());
            }
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testBoundaryMessageEventOnSubprocessAndInsideSubprocessMultiInstance.bpmn20.xml"])]
    public function testBoundaryMessageEventOnSubprocessAndInsideSubprocessMultiInstance(): void
    {
        // this time the boundary events are placed on a user task that is contained inside a sub process
        // and on the subprocess itself

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->assertEquals(17, $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->count());

        // 5 user tasks
        $userTasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertNotNull($userTasks);
        $this->assertEquals(5, count($userTasks));

        // there are 5 event subscriptions to the event on the inner user task
        $executions = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->list();
        $this->assertNotNull($executions);
        $this->assertEquals(5, count($executions));

        // there is a single event subscription for the event on the subprocess
        $executions = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName2")
            ->list();
        $this->assertNotNull($executions);
        $this->assertEquals(1, count($executions));

        // if we complete the outer message event, all inner executions are removed
        $outerScopeExecution = $executions[0];
        $this->runtimeService->messageEventReceived("messageName2", $outerScopeExecution->getId());

        $executions = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName")
            ->list();
        $this->assertEquals(0, count($executions));

        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())
            ->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterOuterMessageBoundary", $userTask->getTaskDefinitionKey());

        $this->taskService->complete($userTask->getId());

        // and we are done
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testBoundaryMessageEventConcurrent.bpmn20.xml"])]
    public function testBoundaryMessageEventConcurrent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("boundaryEvent");

        $eventSubscriptionTask1 = $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->activityId("messageBoundary1")->singleResult();
        $this->assertNotNull($eventSubscriptionTask1);

        $eventSubscriptionTask2 = $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->activityId("messageBoundary2")->singleResult();
        $this->assertNotNull($eventSubscriptionTask2);

        // when I trigger the boundary event for task1
        $this->runtimeService->correlateMessage("task1Message");

        // then the event subscription for task2 still exists
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertNotNull($this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->activityId("messageBoundary2")->singleResult());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageBoundaryEventTest.testExpressionInBoundaryMessageEventName.bpmn20.xml"])]
    public function testExpressionInBoundaryMessageEventName(): void
    {
        // given a process instance with its variables
        $variables = ["foo" => "bar"];
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process", $variables);

        // when message is received
        $execution = $this->runtimeService->createExecutionQuery()
            ->messageEventSubscriptionName("messageName-bar")
            ->singleResult();
        $this->assertNotNull($execution);
        $this->runtimeService->messageEventReceived("messageName-bar", $execution->getId());

        // then then a task should be completed
        $userTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($userTask);
        $this->assertEquals("taskAfterMessage", $userTask->getTaskDefinitionKey());
        $this->taskService->complete($userTask->getId());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->count());
    }
}
