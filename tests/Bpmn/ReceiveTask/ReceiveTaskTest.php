<?php

namespace Tests\Bpmn\SendTask;

use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Event\EventType;
use Jabe\Test\Deployment;
use Tests\Util\PluggableProcessEngineTest;

class ReceiveTaskTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        //initialize all services
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    private function getEventSubscriptionList(string $processInstanceId, ?string $activityId = null): array
    {
        if ($activityId !== null) {
            return $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstanceId)
            ->eventType(EventType::message()->name())->activityId($activityId)->list();
        } else {
            return $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstanceId)
            ->eventType(EventType::message()->name())->list();
        }
    }

    private function getExecutionId(string $processInstanceId, string $activityId): ?string
    {
        return $this->runtimeService->createExecutionQuery()
            ->processInstanceId($processInstanceId)->activityId($activityId)->singleResult()->getId();
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.simpleReceiveTask.bpmn20.xml"])]
    public function testReceiveTaskWithoutMessageReference(): void
    {
        // given: a process instance waiting in the receive task
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there is no message event subscription created for a receive task without a message reference
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // then: we can signal the waiting receive task
        $this->runtimeService->signal($processInstance->getId());

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.singleReceiveTask.bpmn20.xml"])]
    public function testSupportsLegacySignalingOnSingleReceiveTask(): void
    {
        // given: a process instance waiting in the receive task
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there is a message event subscription for the task
        $this->assertEquals(1, count($this->getEventSubscriptionList($processInstance->getId())));

        // then: we can signal the waiting receive task
        $this->runtimeService->signal($this->getExecutionId($processInstance->getId(), "waitState"));

        // expect: subscription is removed
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.singleReceiveTask.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnSingleReceiveTask(): void
    {
        // given: a process instance waiting in the receive task
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there is a message event subscription for the task
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];

        // then: we can trigger the event subscription
        $this->runtimeService->messageEventReceived($subscription->getEventName(), $subscription->getExecutionId());

        // expect: subscription is removed
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.singleReceiveTask.bpmn20.xml"])]
    public function testSupportsCorrelateMessageOnSingleReceiveTask(): void
    {
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        // given: a process instance waiting in the receive task
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess", "bk-" . $id);

        // expect: there is a message event subscription for the task
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];

        // then: we can correlate the event subscription
        $this->runtimeService->correlateMessage($subscription->getEventName(), "bk-" . $id);

        // expect: subscription is removed
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.singleReceiveTask.bpmn20.xml"])]
    public function testSupportsCorrelateMessageByBusinessKeyOnSingleReceiveTask(): void
    {
        // given: a process instance with business key 23 waiting in the receive task
        $processInstance23 = $this->runtimeService->startProcessInstanceByKey("testProcess", "23");

        // given: a 2nd process instance with business key 42 waiting in the receive task
        $processInstance42 = $this->runtimeService->startProcessInstanceByKey("testProcess", "42");

        // expect: there is two message event subscriptions for the tasks
        $subscriptionList1 = $this->getEventSubscriptionList($processInstance23->getId());
        $subscriptionList2 = $this->getEventSubscriptionList($processInstance42->getId());
        $this->assertEquals(2, count($subscriptionList1) + count($subscriptionList2));

        // then: we can correlate the event subscription to one of the process instances
        $this->runtimeService->correlateMessage("newInvoiceMessage", "23");

        // expect: one subscription is removed
        $subscriptionList1 = $this->getEventSubscriptionList($processInstance23->getId());
        $subscriptionList2 = $this->getEventSubscriptionList($processInstance42->getId());
        $this->assertEquals(1, count($subscriptionList1) + count($subscriptionList2));

        // expect: this ends the process instance with business key 23
        $this->testRule->assertProcessEnded($processInstance23->getId());

        // expect: other process instance is still running
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance42->getId())->count());

        // then: we can correlate the event subscription to the other process instance
        $this->runtimeService->correlateMessage("newInvoiceMessage", "42");

        // expect: subscription is removed
        $subscriptionList2 = $this->getEventSubscriptionList($processInstance42->getId());
        $this->assertEquals(0, count($subscriptionList2));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance42->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiSequentialReceiveTask.bpmn20.xml"])]
    public function testSupportsLegacySignalingOnSequentialMultiReceiveTask(): void
    {
        // given: a process instance waiting in the first receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there is a message event subscription for the first task
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];
        $firstSubscriptionId = $subscription->getId();

        // then: we can signal the waiting receive task
        $this->runtimeService->signal($this->getExecutionId($processInstance->getId(), "waitState"));

        // expect: there is a new subscription created for the second receive task instance
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];
        $this->assertFalse($firstSubscriptionId == $subscription->getId());

        // then: we can signal the second waiting receive task
        $this->runtimeService->signal($this->getExecutionId($processInstance->getId(), "waitState"));

        // expect: no event subscription left
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: one user task is created
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($task->getId());

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiSequentialReceiveTask.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnSequentialMultiReceiveTask(): void
    {
        // given: a process instance waiting in the first receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there is a message event subscription for the first task
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];
        $firstSubscriptionId = $subscription->getId();

        // then: we can signal the waiting receive task
        $this->runtimeService->messageEventReceived($subscription->getEventName(), $subscription->getExecutionId());

        // expect: there is a new subscription created for the second receive task instance
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];
        $this->assertFalse($firstSubscriptionId == $subscription->getId());

        // then: we can signal the second waiting receive task
        $this->runtimeService->messageEventReceived($subscription->getEventName(), $subscription->getExecutionId());

        // expect: no event subscription left
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: one user task is created
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($task->getId());

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiSequentialReceiveTask.bpmn20.xml"])]
    public function testSupportsCorrelateMessageOnSequentialMultiReceiveTas(): void
    {
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess", "bk-" . $id);

        // expect: there is a message event subscription for the first task
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];
        $firstSubscriptionId = $subscription->getId();

        // then: we can trigger the event subscription
        $this->runtimeService->correlateMessage($subscription->getEventName(), "bk-" . $id);

        // expect: there is a new subscription created for the second receive task instance
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];
        $this->assertFalse($firstSubscriptionId == $subscription->getId());

        // then: we can signal the second waiting receive task
        $this->runtimeService->correlateMessage($subscription->getEventName(), "bk-" . $id);

        // expect: no event subscription left
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: one user task is created
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($task->getId());

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiParallelReceiveTask.bpmn20.xml"])]
    public function testSupportsLegacySignalingOnParallelMultiReceiveTask(): void
    {
        // given: a process instance waiting in two receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there are two message event subscriptions
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(2, count($subscriptions));

        // expect: there are two executions
        $executions = $this->runtimeService->createExecutionQuery()
            ->processInstanceId($processInstance->getId())->activityId("waitState")
            ->messageEventSubscriptionName("newInvoiceMessage")->list();
        $this->assertEquals(2, count($executions));

        // then: we can signal both waiting receive task
        $this->runtimeService->signal($executions[0]->getId());
        $this->runtimeService->signal($executions[1]->getId());

        // expect: both event subscriptions are removed
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(0, count($subscriptions));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiParallelReceiveTask.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnParallelMultiReceiveTask(): void
    {
        // given: a process instance waiting in two receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there are two message event subscriptions
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(2, count($subscriptions));

        // expect: there are two executions
        $executions = $this->runtimeService->createExecutionQuery()
            ->processInstanceId($processInstance->getId())->activityId("waitState")
            ->messageEventSubscriptionName("newInvoiceMessage")->list();
        $this->assertEquals(2, count($executions));

        // then: we can signal both waiting receive task
        $this->runtimeService->messageEventReceived($subscriptions[0]->getEventName(), $subscriptions[0]->getExecutionId());
        $this->runtimeService->messageEventReceived($subscriptions[1]->getEventName(), $subscriptions[1]->getExecutionId());

        // expect: both event subscriptions are removed
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(0, count($subscriptions));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiParallelReceiveTask.bpmn20.xml"])]
    public function testNotSupportsCorrelateMessageOnParallelMultiReceiveTask(): void
    {
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        // given: a process instance waiting in two receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess", "bk-" . $id);

        // expect: there are two message event subscriptions
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(2, count($subscriptions));

        // then: we can not correlate an event
        $this->expectException(\Exception::class);
        $this->runtimeService->correlateMessage($subscriptions[0]->getEventName(), "bk-" . $id);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiParallelReceiveTaskCompensate.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnParallelMultiReceiveTaskWithCompensation(): void
    {
        // given: a process instance waiting in two receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there are two message event subscriptions
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(2, count($subscriptions));

        // then: we can trigger the first event subscription
        $this->runtimeService->messageEventReceived($subscriptions[0]->getEventName(), $subscriptions[0]->getExecutionId());

        // expect: after completing the first receive task there is one event subscription for compensation
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())
            ->eventType(EventType::compensate()->name())->count());

        // then: we can trigger the second event subscription
        $this->runtimeService->messageEventReceived($subscriptions[1]->getEventName(), $subscriptions[1]->getExecutionId());

        // expect: there are three event subscriptions for compensation (two subscriptions for tasks and one for miBody)
        $this->assertEquals(3, $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())
        ->eventType(EventType::compensate()->name())->count());

        // expect: one user task is created
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($task->getId());

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiParallelReceiveTaskBoundary.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnParallelMultiInstanceWithBoundary(): void
    {
        // given: a process instance waiting in two receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there are three message event subscriptions
        $this->assertEquals(3, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: there are two message event subscriptions for the receive tasks
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId(), "waitState");
        $this->assertEquals(2, count($subscriptions));

        // then: we can trigger both receive task event subscriptions
        $this->runtimeService->messageEventReceived($subscriptions[0]->getEventName(), $subscriptions[0]->getExecutionId());
        $this->runtimeService->messageEventReceived($subscriptions[1]->getEventName(), $subscriptions[1]->getExecutionId());

        // expect: all subscriptions are removed (boundary subscription is removed too)
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.subProcessReceiveTask.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnSubProcessReceiveTask(): void
    {
        // given: a process instance waiting in the sub-process receive task
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there is a message event subscription for the task
        $subscriptionList = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(1, count($subscriptionList));
        $subscription = $subscriptionList[0];

        // then: we can trigger the event subscription
        $this->runtimeService->messageEventReceived($subscription->getEventName(), $subscription->getExecutionId());

        // expect: subscription is removed
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.multiSubProcessReceiveTask.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnMultiSubProcessReceiveTask(): void
    {
        // given: a process instance waiting in two parallel sub-process receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there are two message event subscriptions
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(2, count($subscriptions));

        // then: we can trigger both receive task event subscriptions
        $this->runtimeService->messageEventReceived($subscriptions[0]->getEventName(), $subscriptions[0]->getExecutionId());
        $this->runtimeService->messageEventReceived($subscriptions[1]->getEventName(), $subscriptions[1]->getExecutionId());

        // expect: subscriptions are removed
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.parallelGatewayReceiveTask.bpmn20.xml"])]
    public function testSupportsMessageEventReceivedOnReceiveTaskBehindParallelGateway(): void
    {
        // given: a process instance waiting in two receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess");

        // expect: there are two message event subscriptions
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(2, count($subscriptions));

        // then: we can trigger both receive task event subscriptions
        $this->runtimeService->messageEventReceived($subscriptions[0]->getEventName(), $subscriptions[0]->getExecutionId());
        $this->runtimeService->messageEventReceived($subscriptions[1]->getEventName(), $subscriptions[1]->getExecutionId());

        // expect: subscriptions are removed
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.parallelGatewayReceiveTask.bpmn20.xml"])]
    public function testSupportsCorrelateMessageOnReceiveTaskBehindParallelGateway(): void
    {
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        // given: a process instance waiting in two receive tasks
        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess", "bk-" . $id);

        // expect: there are two message event subscriptions
        $subscriptions = $this->getEventSubscriptionList($processInstance->getId());
        $this->assertEquals(2, count($subscriptions));

        // then: we can trigger both receive task event subscriptions
        $this->runtimeService->correlateMessage($subscriptions[0]->getEventName(), "bk-" . $id);
        $this->runtimeService->correlateMessage($subscriptions[1]->getEventName(), "bk-" . $id);

        // expect: subscriptions are removed
        $this->assertEquals(0, count($this->getEventSubscriptionList($processInstance->getId())));

        // expect: this ends the process instance
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/ReceiveTask/ReceiveTaskTest.testWaitStateBehavior.bpmn20.xml"])]
    public function testWaitStateBehavior(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("receiveTask");
        $execution = $this->runtimeService->createExecutionQuery()
          ->processInstanceId($pi->getId())
          ->activityId("waitState")
          ->singleResult();
        $this->assertNotNull($execution);

        $this->runtimeService->signal($execution->getId());
        $this->testRule->assertProcessEnded($pi->getId());
    }
}
