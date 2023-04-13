<?php

namespace Tests\Bpmn\Event\Signal;

use Bpmn\Bpmn;
use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\EventSubscriptionQueryImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Event\EventType;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface,
    CommandInvocationContext
};
use Jabe\Impl\Util\{
    ClockUtil,
    StringUtil
};
use Jabe\Test\Deployment;
use Jabe\Variable\{
    SerializationDataFormats,
    Variables
};
use Tests\Api\Variables\FailingPhpSerializable;
use Tests\Bpmn\ExecutionListener\RecorderExecutionListener;
use Tests\Util\PluggableProcessEngineTest;

class SignalEventTest extends PluggableProcessEngineTest
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

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignal.bpmn20.xml"])]
    public function testSignalCatchIntermediate(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchSignal");

        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignal");

        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignalBoundary.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignal.bpmn20.xml"])]
    public function testSignalCatchBoundary(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchSignal");

        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignal");

        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignalBoundaryWithReceiveTask.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignal.bpmn20.xml"])]
    public function testSignalCatchBoundaryWithVariables(): void
    {
        $variables1 = [];
        $variables1["processName"] = "catchSignal";
        $pi = $this->runtimeService->startProcessInstanceByKey("catchSignal", $variables1);

        $variables2 = [];
        $variables2["processName"] = "throwSignal";
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignal", $variables2);

        $this->assertEquals("catchSignal", $this->runtimeService->getVariable($pi->getId(), "processName"));
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal2.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch2.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal3.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch3.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal4.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch4.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal5.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch5.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal6.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch6.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal7.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch7.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal8.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch8.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal9.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch9.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignal10.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsynch10.bpmn20.xml"])]
    public function testSignalCatchIntermediateAsynch(): void
    {
        $processInstance1 = $this->runtimeService->startProcessInstanceByKey("catchSignal");
        $processInstance12 = $this->runtimeService->startProcessInstanceByKey("catchSignal2");
        $processInstance13 = $this->runtimeService->startProcessInstanceByKey("catchSignal3");
        $processInstance14 = $this->runtimeService->startProcessInstanceByKey("catchSignal4");
        $processInstance15 = $this->runtimeService->startProcessInstanceByKey("catchSignal5");
        $processInstance16 = $this->runtimeService->startProcessInstanceByKey("catchSignal6");
        $processInstance17 = $this->runtimeService->startProcessInstanceByKey("catchSignal7");
        $processInstance18 = $this->runtimeService->startProcessInstanceByKey("catchSignal8");
        $processInstance19 = $this->runtimeService->startProcessInstanceByKey("catchSignal9");
        $processInstance110 = $this->runtimeService->startProcessInstanceByKey("catchSignal10");

        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance1->getId())->count());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance1->getId())->count());

        $processInstance2 = $this->runtimeService->startProcessInstanceByKey("throwSignal");
        $processInstance22 = $this->runtimeService->startProcessInstanceByKey("throwSignal2");
        $processInstance23 = $this->runtimeService->startProcessInstanceByKey("throwSignal3");
        $processInstance24 = $this->runtimeService->startProcessInstanceByKey("throwSignal4");
        $processInstance25 = $this->runtimeService->startProcessInstanceByKey("throwSignal5");
        $processInstance26 = $this->runtimeService->startProcessInstanceByKey("throwSignal6");
        $processInstance27 = $this->runtimeService->startProcessInstanceByKey("throwSignal7");
        $processInstance28 = $this->runtimeService->startProcessInstanceByKey("throwSignal8");
        $processInstance29 = $this->runtimeService->startProcessInstanceByKey("throwSignal9");
        $processInstance210 = $this->runtimeService->startProcessInstanceByKey("throwSignal10");

        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance1->getId())->count() + $this->createEventSubscriptionQuery()->processInstanceId($processInstance2->getId())->count());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance1->getId())->count() + $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance2->getId())->count());

        // there is a job:
        $this->assertEquals(1, $this->managementService->createJobQuery()->processInstanceId($processInstance1->getId())->count() + $this->managementService->createJobQuery()->processInstanceId($processInstance2->getId())->count());
        try {
            $now = new \DateTime('now');
            //$this->testRule->waitForJobExecutorToProcessAllJobs($processInstance110->getId(), 120000);
            sleep(120);
            $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance1->getId())->count());
            $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance1->getId())->count());
            $this->assertEquals(0, $this->managementService->createJobQuery()->processInstanceId($processInstance1->getId())->count());
        } finally {
            ClockUtil::setCurrentTime(new \DateTime('now'), ...$this->processEngineConfiguration->getJobExecutorState());
        }
    }

    public function createEventSubscriptionQuery(): EventSubscriptionQueryImpl
    {
        return new EventSubscriptionQueryImpl($this->processEngineConfiguration->getCommandExecutorTxRequired());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchMultipleSignalsDifferent.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalDifferent.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAbortSignalDifferent.bpmn20.xml"])]
    public function testSignalCatchDifferentSignals(): void
    {
        $processInstance1 = $this->runtimeService->startProcessInstanceByKey("catchSignalDifferent");
        $this->assertEquals(2, $this->createEventSubscriptionQuery()->processInstanceId($processInstance1->getId())->count());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance1->getId())->count());

        $processInstance2 = $this->runtimeService->startProcessInstanceByKey("throwAbortDifferent");

        sleep(120);
        $this->assertEquals(1, $this->createEventSubscriptionQuery()->processInstanceId($processInstance1->getId())->eventName('alert different')->count());
        $this->assertEquals(1, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance1->getId())->count() + $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance2->getId())->count());

        $taskAfterAbort = $this->taskService->createTaskQuery()->processInstanceId($processInstance1->getId())->taskAssignee("gonzo")->singleResult();
        $this->assertNotNull($taskAfterAbort);
        $this->taskService->complete($taskAfterAbort->getId());

        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignalDifferent");

        $this->assertEquals(0, $this->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->count());
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    /**
     * Verifies the solution of https://jira.codehaus.org/browse/ACT-1309
     */
    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Signal/SignalEventTest.testSignalBoundaryOnSubProcess.bpmn20.xml"])]
    public function testSignalBoundaryOnSubProcess(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("signalEventOnSubprocess");
        $this->runtimeService->signalEventReceived("stopSignal");
        $this->testRule->assertProcessEnded($pi->getProcessInstanceId());
        $this->assertTrue(true);
    }

    /**
     * TestCase to reproduce Issue ACT-1344
     */
    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Signal/SignalEventTest.testNonInterruptingSignal.bpmn20.xml"])]
    public function testNonInterruptingSignal(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("nonInterruptingSignalEvent");

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getProcessInstanceId())->list();
        $this->assertEquals(1, count($tasks));
        $currentTask = $tasks[0];
        $this->assertEquals("My User Task", $currentTask->getName());

        $this->runtimeService->signalEventReceived("alert");

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getProcessInstanceId())->list();
        $this->assertEquals(2, count($tasks));

        $this->taskService->complete($this->taskService->createTaskQuery()->taskName("My User Task")->singleResult()->getId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getProcessInstanceId())->list();
        $this->assertEquals(1, count($tasks));
        $currentTask = $tasks[0];
        $this->assertEquals("My Second User Task", $currentTask->getName());
        $this->taskService->complete($currentTask->getId());
    }


    /**
     * TestCase to reproduce Issue ACT-1344
     */
    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Signal/SignalEventTest.testNonInterruptingSignalWithSubProcess.bpmn20.xml"])]
    public function testNonInterruptingSignalWithSubProcess(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("nonInterruptingSignalWithSubProcess");
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getProcessInstanceId())->list();
        $this->assertEquals(1, count($tasks));

        $currentTask = $tasks[0];
        $this->assertEquals("Approve", $currentTask->getName());

        $this->runtimeService->signalEventReceived("alert");

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getProcessInstanceId())->list();
        $this->assertEquals(2, count($tasks));

        $this->taskService->complete($this->taskService->createTaskQuery()->taskName("Approve")->singleResult()->getId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getProcessInstanceId())->list();
        $this->assertEquals(1, count($tasks));

        $currentTask = $tasks[0];
        $this->assertEquals("Review", $currentTask->getName());

        $this->taskService->complete($this->taskService->createTaskQuery()->taskName("Review")->singleResult()->getId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getProcessInstanceId())->list();
        $this->assertEquals(1, count($tasks));
        $this->taskService->complete($tasks[0]->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Signal/SignalEventTest.testSignalStartEventInEventSubProcess.bpmn20.xml"])]
    public function testSignalStartEventInEventSubProcess(): void
    {
        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("signalStartEventInEventSubProcess");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        // send interrupting signal to event sub process
        $this->runtimeService->signalEventReceived("alert");

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task doesn't exist because signal start event is interrupting
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $taskQuery->count());

        // check if execution doesn't exist because signal start event is interrupting
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $executionQuery->count());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Signal/SignalEventTest.testNonInterruptingSignalStartEventInEventSubProcess.bpmn20.xml"])]
    public function testNonInterruptingSignalStartEventInEventSubProcess(): void
    {
        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nonInterruptingSignalStartEventInEventSubProcess");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        // send non interrupting signal to event sub process
        $this->runtimeService->signalEventReceived("alert");

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task still exists because signal start event is non interrupting
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        // check if execution still exists because signal start event is non interrupting
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        $task = $taskQuery->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.testSignalStartEventSimple.bpmn20.xml"])]
    public function testSignalStartEventSimple(): void
    {
        // event subscription for signal start event
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->eventType("signal")->eventName("alert simple")->count());

        $this->runtimeService->signalEventReceived("alert simple");
        sleep(20);
        // the signal should start a new process instance
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("start process task simple")->count());
        $task = $this->taskService->createTaskQuery()->taskName("start process task simple")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.testSuspendedProcessWithSignalStartEvent.bpmn20.xml"])]
    public function testSuspendedProcessWithSignalStartEvent(): void
    {
        // event subscription for signal start event
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->eventType("signal")->eventName("alert for suspended")->count());

        $processDefinitions = $this->repositoryService->createProcessDefinitionQuery()->processDefinitionKey("startBySignalSuspendedProcess")->list();
        foreach ($processDefinitions as $processDefinition) {
            $this->repositoryService->suspendProcessDefinitionById($processDefinition->getId());
        }

        $this->runtimeService->signalEventReceived("alert for suspended");
        sleep(15);
        // the signal should not start a process instance for the suspended process definition
        $this->assertEquals(0, $this->taskService->createTaskQuery()->taskName("start process task for suspended")->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.signalStartEventOther.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTest.testOtherSignalStartEvent.bpmn20.xml"])]
    public function testMultipleProcessesWithSameSignalStartEvent(): void
    {
        // event subscriptions for signal start event
        $this->assertEquals(2, $this->runtimeService->createEventSubscriptionQuery()->eventType("signal")->eventName("alert other")->count());

        $this->runtimeService->signalEventReceived("alert other");
        sleep(15);
        // the signal should start new process instances for both process definitions
        $this->assertEquals(2, $this->taskService->createTaskQuery()->taskName("start process task other")->count());
        $tasks = $this->taskService->createTaskQuery()->taskName("start process task other")->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.testSignalStartEventSimpleIntermediate.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalSimpleIntermediate.bpmn20.xml"])]
    public function testStartProcessInstanceBySignalFromIntermediateThrowingSignalEvent(): void
    {
        // start a process instance to throw a signal
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignal_1");
        sleep(120);
        // the signal should start a new process instance
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("start process task simple intermediate")->count());
        $task = $this->taskService->createTaskQuery()->taskName("start process task simple intermediate")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.testSignalStartEventSimple.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalSimple.bpmn20.xml"])]
    public function testIntermediateThrowingSignalEventWithSuspendedSignalStartEvent(): void
    {
        // event subscription for signal start event
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->eventType("signal")->eventName("alert simple")->count());

        $processDefinitions = $this->repositoryService->createProcessDefinitionQuery()->processDefinitionKey("startBySignal1_02")->list();

        foreach ($processDefinitions as $processDefinition) {
            $this->repositoryService->suspendProcessDefinitionById($processDefinition->getId());
        }

        // start a process instance to throw a signal
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignal");
        // the signal should not start a new process instance of the suspended process definition
        sleep(15);
        $this->assertEquals(0, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.testProcessesWithMultipleSignalStartEvents.bpmn20.xml"])]
    public function testProcessesWithMultipleSignalStartEvents(): void
    {
        $this->assertTrue(true);
        // event subscriptions for signal start event
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->eventName("alertMulti")->count());
        $this->assertEquals(1, $this->runtimeService->createEventSubscriptionQuery()->eventName("abortMulti")->count());

        $this->runtimeService->signalEventReceived("alertMulti");
        sleep(15);
        // the signal should start new process instances for both process definitions
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("multiple start signal events alert task")->count());
        $tasks = $this->taskService->createTaskQuery()->taskName("multiple start signal events alert task")->list();
        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertTwiceAndTerminate.bpmn20.xml"])]
    public function testThrowSignalMultipleCancellingReceivers(): void
    {
        RecorderExecutionListener::clear();

        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchAlertTwiceAndTerminate");

        // event subscription for intermediate signal events
        $this->assertEquals(2, $this->runtimeService->createEventSubscriptionQuery()->eventType("signal")->eventName("alert")->processInstanceId($processInstance->getId())->count());

        // try to send 'alert' signal to both executions
        $this->runtimeService->signalEventReceived("alert");

        // then only one terminate end event was executed
        $this->assertEquals(1, count(RecorderExecutionListener::getRecordedEvents()));

        // and instances ended successfully
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertTwiceAndTerminateTwice.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalTwice.bpmn20.xml"])]
    public function testIntermediateThrowSignalMultipleCancellingReceivers(): void
    {
        //RecorderExecutionListener::clear();

        $processInstance1 = $this->runtimeService->startProcessInstanceByKey("catchAlertTwiceAndTerminate");

        // event subscriptions for intermediate events
        $this->assertEquals(2, $this->runtimeService->createEventSubscriptionQuery()->eventType("signal")->eventName("alert twice")->processInstanceId($processInstance1->getId())->count());

        // started process instance try to send 'alert' signal to both executions
        $processInstance2 = $this->runtimeService->startProcessInstanceByKey("throwSignal_123");

        sleep(30);

        // then only one terminate end event was executed
        // $this->assertEquals(1, count(RecorderExecutionListener::getRecordedEvents()));

        // and both instances ended successfully
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance1->getId())->count() + $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance2->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.signalStartEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsync.bpmn20.xml"])]
    public function testAsyncSignalStartEventJobProperties(): void
    {
        $this->processEngineConfiguration->setEnsureJobDueDateNotNull(false);

        /*$catchingProcessDefinition = $this->repositoryService
        ->createProcessDefinitionQuery()
        ->processDefinitionKey("startBySignal1")
        ->singleResult();*/

        // given a process instance that throws a signal asynchronously
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignalAsync");
        // where the throwing instance ends immediately

        // then there is not yet a catching process instance
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        // but there is a job for the asynchronous continuation
        $asyncJob = $this->managementService->createJobQuery()->processDefinitionKey('startBySignal1')->singleResult();
        //$this->assertEquals($catchingProcessDefinition->getId(), $asyncJob->getProcessDefinitionId());
        //$this->assertEquals($catchingProcessDefinition->getKey(), $asyncJob->getProcessDefinitionKey());
        $this->assertNull($asyncJob->getExceptionMessage());
        $this->assertNull($asyncJob->getExecutionId());
        $this->assertNull($asyncJob->getJobDefinitionId());
        $this->assertEquals(0, $asyncJob->getPriority());
        $this->assertNull($asyncJob->getProcessInstanceId());
        $this->assertEquals(3, $asyncJob->getRetries());
        $this->assertNull($asyncJob->getDuedate());
        $this->assertNull($asyncJob->getDeploymentId());
        sleep(15);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.signalStartEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsync.bpmn20.xml"])]
    public function testAsyncSignalStartEventJobPropertiesDueDateSet(): void
    {
        $testTime = (new \DateTime())->setTimestamp(1457326800);
        ClockUtil::setCurrentTime($testTime, ...$this->processEngineConfiguration->getJobExecutorState());
        $this->processEngineConfiguration->setEnsureJobDueDateNotNull(true);

        /*$catchingProcessDefinition = $this->repositoryService
            ->createProcessDefinitionQuery()
            ->processDefinitionKey("startBySignal1")
            ->singleResult();*/

        // given a process instance that throws a signal asynchronously
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignalAsync");
        // where the throwing instance ends immediately

        // then there is not yet a catching process instance
        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        // but there is a job for the asynchronous continuation
        $asyncJob = $this->managementService->createJobQuery()->processDefinitionKey('startBySignal1')->singleResult();
        //$this->assertEquals($catchingProcessDefinition->getId(), $asyncJob->getProcessDefinitionId());
        //$this->assertEquals($catchingProcessDefinition->getKey(), $asyncJob->getProcessDefinitionKey());
        $this->assertNull($asyncJob->getExceptionMessage());
        $this->assertNull($asyncJob->getExecutionId());
        $this->assertNull($asyncJob->getJobDefinitionId());
        $this->assertEquals(0, $asyncJob->getPriority());
        $this->assertNull($asyncJob->getProcessInstanceId());
        $this->assertEquals(3, $asyncJob->getRetries());
        $this->assertEquals($testTime, new \DateTime($asyncJob->getDuedate()));
        $this->assertNull($asyncJob->getDeploymentId());
        sleep(15);
        ClockUtil::setCurrentTime(new \DateTime('now'), ...$this->processEngineConfiguration->getJobExecutorState());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.signalStartEventAsync.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsyncAndStart.bpmn20.xml"])]
    public function testAsyncSignalStartEvent(): void
    {
        /*$catchingProcessDefinition = $this->repositoryService
            ->createProcessDefinitionQuery()
            ->processDefinitionKey("startBySignal1")
            ->singleResult();*/

        // given a process instance that throws a signal asynchronously
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignalAsync_55");

        // with an async job to trigger the signal event
        //$job = $this->managementService->createJobQuery()->processDefinitionKey('startBySignal1')->singleResult();

        // wait when the job is executed
        sleep(90);
        //$this->managementService->executeJob($job->getId());

        // then there is a process instance
        //$processInstance = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult();
        //$this->assertNotNull($processInstance);
        //$this->assertEquals($catchingProcessDefinition->getId(), $processInstance->getProcessDefinitionId());

        // and a task
        //$this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskName("start process task async")->count());
        $task = $this->taskService->createTaskQuery()->taskName("start process task async")->singleResult();
        $this->taskService->complete($task->getId());
    }

    /**
     * CAM-4527
     */
    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.testNoContinuationWhenSignalInterruptsThrowingActivity.bpmn20.xml"])]
    public function testNoContinuationWhenSignalInterruptsThrowingActivity(): void
    {
        // given a process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("signalEventSubProcess");

        // when throwing a signal in the sub process that interrupts the subprocess
        $subProcessTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($subProcessTask->getId());
        sleep(15);
        // then execution should not have been continued after the subprocess
        $this->assertEquals(0, $this->taskService->createTaskQuery()->taskDefinitionKey("afterSubProcessTask")->count());
        $this->assertEquals(1, $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->count());
        $task = $this->taskService->createTaskQuery()->taskDefinitionKey("eventSubProcessTask")->singleResult();
        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.signalStartEventWithVariables.bpmn20.xml"])]
    public function testSetSerializedVariableValues(): void
    {
        // when
        $phpSerializable = new FailingPhpSerializable("foo");

        $serializedObject = str_replace('\\', '.', serialize($phpSerializable));

        // but it can be set as a variable when delivering a message:
        $this->runtimeService
            ->signalEventReceived(
                "alert variables",
                null,
                ["var" => Variables::serializedObjectValue($serializedObject)
                        ->objectTypeName(FailingPhpSerializable::class)
                        ->serializationDataFormat(SerializationDataFormats::PHP)
                        ->create()
                ]
            );

        sleep(15);

        $task = $this->taskService->createTaskQuery()->taskName("start process task with variables")->singleResult();
        $processInstanceId = $task->getProcessInstanceId();
        // then
        $startedInstance = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstanceId)->singleResult();
        $this->assertNotNull($startedInstance);

        $variableTyped = $this->runtimeService->getVariableTyped($startedInstance->getId(), "var", false);
        $this->assertNotNull($variableTyped);
        $this->assertFalse($variableTyped->isDeserialized());
        $this->assertEquals($serializedObject, $variableTyped->getValueSerialized());
        $this->assertEquals(FailingPhpSerializable::class, $variableTyped->getObjectTypeName());
        $this->assertEquals(SerializationDataFormats::PHP, $variableTyped->getSerializationDataFormat());

        $this->taskService->complete($task->getId());
    }

    /**
     * CAM-6807
     */
    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTests.catchAlertSignalBoundary.bpmn20.xml", "tests/Resources/Bpmn/Event/Signal/SignalEventTests.throwAlertSignalAsync.bpmn20.xml"])]
    private function testAsyncSignalBoundary(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("catchSignal");

        // given a process instance that throws a signal asynchronously
        $processInstance = $this->runtimeService->startProcessInstanceByKey("throwSignalAsync");

        sleep(30);
        $job = $this->managementService->createJobQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($job);  // Throws Exception!

        // when the job is executed
        $this->managementService->executeJob($job->getId());

        // then there is a process instance
        $processInstance = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($processInstance);
        // assertEquals($catchingProcessDefinition->getId(), processInstance->getProcessDefinitionId());

        // and a task
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Signal/SignalEventTest.testThrownSignalInEventSubprocessInSubprocess.bpmn20.xml"])]
    public function testThrownSignalInEventSubprocessInSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("embeddedEventSubprocess");

        $taskBefore = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($taskBefore);
        $this->assertEquals("task in subprocess", $taskBefore->getName());

        //sleep(90);
        $job = $this->managementService->createJobQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($job);

        $this->managementService->executeJob($job->getId());

        $taskAfter = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($taskAfter);
        $this->assertEquals("after catch", $taskAfter->getName());

        $jobAfter = $this->managementService->createJobQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNull($jobAfter);

        $this->taskService->complete($taskAfter->getId());
    }
}
