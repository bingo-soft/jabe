<?php

namespace Tests\Bpmn\Event\Compensate;

use Bpmn\Bpmn;
use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Event\EventType;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface,
    CommandInvocationContext
};
use Jabe\Test\{
    Deployment,
    RequiredHistoryLevel
};
use Jabe\Variable\Variables;
use Tests\Bpmn\Event\Compensate\Helper\{
    BookFlightService,
    CancelFlightService,
    GetVariablesDelegate,
    SetVariablesDelegate
};
use Tests\Util\PluggableProcessEngineTest;

class CompensateEventTest extends PluggableProcessEngineTest
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
        /*$deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }*/
    }

    public function testCompensateOrder(): void
    {
        //given two process models, only differ in order of the activities
        $PROCESS_MODEL_WITH_REF_BEFORE = "tests/Resources/Bpmn/Event/Compensate/compensation_reference-before.bpmn";
        $PROCESS_MODEL_WITH_REF_AFTER = "tests/Resources/Bpmn/Event/Compensate/compensation_reference-after.bpmn";

        //when model with ref before is deployed
        $deployment1 = $this->repositoryService->createDeployment()
                ->addClasspathResource($PROCESS_MODEL_WITH_REF_BEFORE)
                ->deploy();
        //then no problem will occure

        //when model with ref after is deployed
        $deployment2 = $this->repositoryService->createDeployment()
                ->addClasspathResource($PROCESS_MODEL_WITH_REF_AFTER)
                ->deploy();
        //then also no problem should occure

        //clean up
        $this->repositoryService->deleteDeployment($deployment1->getId());
        $this->repositoryService->deleteDeployment($deployment2->getId());

        $this->assertTrue(true);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateSubprocess.bpmn20.xml"])]
    public function testCompensateSubprocess123(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        $this->assertEquals(5, $this->runtimeService->getVariable($processInstance->getId(), "undoBookHotel"));

        $execution = $this->runtimeService->createExecutionQuery()
                          ->processInstanceId($processInstance->getId())->activityId("beforeEnd")->singleResult();

        $this->runtimeService->signal($execution->getId(), null, ["hello" => "world"], ["hi" => "there"]); /* $processInstance->getId() -> same, hi => there will go to variables */
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateSubprocessInsideSubprocess.bpmn20.xml"])]
    public function testCompensateSubprocessInsideSubprocess(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();
        $this->completeTask($processInstanceId, "Book Hotel");
        $this->completeTask($processInstanceId, "Book Flight");

        // throw compensation event
        $this->completeTask($processInstanceId, "throw compensation");

        // execute compensation handlers
        $this->completeTask($processInstanceId, "Cancel Hotel");
        $this->completeTask($processInstanceId, "Cancel Flight");

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateParallelSubprocess.bpmn20.xml"])]
    public function testCompensateParallelSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        $this->assertEquals(5, $this->runtimeService->getVariable($processInstance->getId(), "undoBookHotel"));

        $singleResult = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($singleResult->getId());

        $this->runtimeService->signal($processInstance->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateParallelSubprocessCompHandlerWaitstate.bpmn20.xml"])]
    public function testCompensateParallelSubprocessCompHandlerWaitstate(): void
    {

        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        $compensationHandlerTasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("undoBookHotel")->list();
        $this->assertEquals(5, count($compensationHandlerTasks));

        $rootActivityInstance = $this->runtimeService->getActivityInstance($processInstance->getId());
        $compensationHandlerInstances = $this->getInstancesForActivityId($rootActivityInstance, "undoBookHotel");
        $this->assertEquals(5, count($compensationHandlerInstances));

        foreach ($compensationHandlerTasks as $task) {
            $this->taskService->complete($task->getId());
        }

        $singleResult = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($singleResult->getId());

        $this->runtimeService->signal($processInstance->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateParallelSubprocessCompHandlerWaitstate.bpmn20.xml"])]
    public function testDeleteParallelSubprocessCompHandlerWaitstate(): void
    {
        // given
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        // five inner tasks
        $compensationHandlerTasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("undoBookHotel")->list();
        $this->assertEquals(5, count($compensationHandlerTasks));

        // when
        $this->runtimeService->deleteProcessInstance($processInstance->getId(), "");

        // then the process has been removed
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateMiSubprocess.bpmn20.xml"])]
    public function testCompensateMiSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        $this->assertEquals(5, $this->runtimeService->getVariable($processInstance->getId(), "undoBookHotel"));

        $this->runtimeService->signal($processInstance->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateScope.bpmn20.xml"])]
    public function testCompensateScope(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");
        $this->assertEquals(5, $this->runtimeService->getVariable($processInstance->getId(), "undoBookHotel"));
        $this->assertEquals(5, $this->runtimeService->getVariable($processInstance->getId(), "undoBookFlight"));
        $this->runtimeService->signal($processInstance->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateActivityRef.bpmn20.xml"])]
    public function testCompensateActivityRef(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");
        $this->assertEquals(5, $this->runtimeService->getVariable($processInstance->getId(), "undoBookHotel"));
        $this->assertNull($this->runtimeService->getVariable($processInstance->getId(), "undoBookFlight"));
        $this->runtimeService->signal($processInstance->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateSubprocessWithBoundaryEvent.bpmn20.xml"])]
    public function testCompensateSubprocessWithBoundaryEvent(): void
    {
        $instance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        $compensationTask = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->singleResult();
        $this->assertNotNull($compensationTask);
        $this->assertEquals("undoSubprocess", $compensationTask->getTaskDefinitionKey());

        $this->taskService->complete($compensationTask->getId());
        $this->runtimeService->signal($instance->getId());
        $this->testRule->assertProcessEnded($instance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateActivityInSubprocess.bpmn20.xml"])]
    public function testCompensateActivityInSubprocess(): void
    {
        // given
        $instance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        $scopeTask = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->singleResult();
        $this->taskService->complete($scopeTask->getId());

        // process has not yet thrown compensation
        // when throw compensation
        $this->runtimeService->signal($instance->getId());
        // then
        $compensationTask = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->singleResult();
        $this->assertNotNull($compensationTask);
        $this->assertEquals("undoScopeTask", $compensationTask->getTaskDefinitionKey());

        $this->taskService->complete($compensationTask->getId());
        $this->runtimeService->signal($instance->getId());
        $this->testRule->assertProcessEnded($instance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateActivityInConcurrentSubprocess.bpmn20.xml"])]
    public function testCompensateActivityInConcurrentSubprocess(): void
    {
        // given
        $instance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        $scopeTask = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->taskDefinitionKey("scopeTask")->singleResult();
        $this->taskService->complete($scopeTask->getId());

        $outerTask = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->taskDefinitionKey("outerTask")->singleResult();
        $this->taskService->complete($outerTask->getId());

        // process has not yet thrown compensation
        // when throw compensation
        $this->runtimeService->signal($instance->getId());

        // then
        $compensationTask = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->singleResult();
        $this->assertNotNull($compensationTask);
        $this->assertEquals("undoScopeTask", $compensationTask->getTaskDefinitionKey());

        $this->taskService->complete($compensationTask->getId());
        $this->runtimeService->signal($instance->getId());
        $this->testRule->assertProcessEnded($instance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateConcurrentMiActivity.bpmn20.xml"])]
    public function testCompensateConcurrentMiActivity(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();

        // complete 4 of 5 user tasks
        $this->completeTasks($processInstanceId, "Book Hotel", 4);

        // throw compensation event
        $this->completeTaskWithVariable($processInstanceId, "Request Vacation", "accept", false);

        // should not compensate activity before multi instance activity is completed
        $this->assertEquals(0, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->taskName("Cancel Hotel")->count());

        // complete last open task and end process instance
        $this->completeTask($processInstanceId, "Book Hotel");
        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateConcurrentMiSubprocess.bpmn20.xml"])]
    public function testCompensateConcurrentMiSubprocess(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();

        // complete 4 of 5 user tasks
        $this->completeTasks($processInstanceId, "Book Hotel", 4);

        // throw compensation event
        $this->completeTaskWithVariable($processInstanceId, "Request Vacation", "accept", false);

        // should not compensate activity before multi instance activity is completed
        $this->assertEquals(0, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->taskName("Cancel Hotel")->count());

        // complete last open task and end process instance
        $this->completeTask($processInstanceId, "Book Hotel");

        $this->runtimeService->signal($processInstanceId);
        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateActivityRefMiActivity.bpmn20.xml"])]
    public function testCompensateActivityRefMiActivity(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();

        $this->completeTasks($processInstanceId, "Book Hotel", 5);

        // throw compensation event for activity
        $this->completeTaskWithVariable($processInstanceId, "Request Vacation", "accept", false);

        // execute compensation handlers for each execution of the subprocess
        $this->assertEquals(5, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());
        $this->completeTasks($processInstanceId, "Cancel Hotel", 5);

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateActivityRefMiSubprocess.bpmn20.xml"])]
    public function testCompensateActivityRefMiSubprocess(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();

        $this->completeTasks($processInstanceId, "Book Hotel", 5);

        // throw compensation event for activity
        $this->completeTaskWithVariable($processInstanceId, "Request Vacation", "accept", false);

        // execute compensation handlers for each execution of the subprocess
        $this->assertEquals(5, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());
        $this->completeTasks($processInstanceId, "Cancel Hotel", 5);

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCallActivityCompensationHandler.bpmn20.xml", "tests/Resources/Bpmn/Event/Compensate/CompensationHandler.bpmn20.xml"])]
    public function testCallActivityCompensationHandler(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        if ($this->processEngineConfiguration->getHistory() != ProcessEngineConfiguration::HISTORY_NONE) {
            $this->assertEquals(5, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookHotel")->count());
        }

        $this->runtimeService->signal($processInstance->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());

        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());

        if ($this->processEngineConfiguration->getHistory() != ProcessEngineConfiguration::HISTORY_NONE) {
            $this->assertEquals(5, $this->historyService->createHistoricProcessInstanceQuery()->superProcessInstanceId($processInstance->getId())->count());
            $this->assertEquals(1, $this->historyService->createHistoricProcessInstanceQuery()->processInstanceId($processInstance->getId())->count());
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateMiSubprocessVariableSnapshots.bpmn20.xml"])]
    public function testCompensateMiSubprocessVariableSnapshots(): void
    {
        // see referenced php delegates in the process definition.

        $hotels = ["Rupert", "Vogsphere", "Milliways", "Taunton", "Ysolldins"];

        SetVariablesDelegate::setValues($hotels);

        // SetVariablesDelegate take the first element of static list and set the value as local variable
        // GetVariablesDelegate read the variable and add the value to static list

        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        if ($this->processEngineConfiguration->getHistory() != ProcessEngineConfiguration::HISTORY_NONE) {
            $this->assertEquals(5, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookHotel")->count());
        }

        foreach ($hotels as $hotel) {
            $this->assertTrue(in_array($hotel, GetVariablesDelegate::$values));
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateMiSubprocessWithCompensationEventSubprocessVariableSnapshots.bpmn20.xml"])]
    public function testCompensateMiSubprocessWithCompensationEventSubprocessVariableSnapshots(): void
    {
        // see referenced php delegates in the process definition.

        $hotels = ["Rupert", "Vogsphere", "Milliways", "Taunton", "Ysolldins"];

        SetVariablesDelegate::setValues($hotels);

        // SetVariablesDelegate take the first element of static list and set the value as local variable
        // GetVariablesDelegate read the variable and add the value to static list

        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        if ($this->processEngineConfiguration->getHistory() != ProcessEngineConfiguration::HISTORY_NONE) {
            $this->assertEquals(5, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookHotel")->count());
        }

        foreach ($hotels as $hotel) {
            $this->assertTrue(in_array($hotel, GetVariablesDelegate::$values));
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateMiSubprocessVariableSnapshotOfElementVariable.bpmn20.xml"])]
    private function testCompensateMiSubprocessVariableSnapshotOfElementVariable(): void
    {
        $variables = [];
        // multi instance collection
        $flights = ["STS-14", "STS-28"];
        $variables["flights"] = &$flights;

        // see referenced php delegates in the process definition
        // php delegates read element variable (flight) and add the variable value
        // to a static list
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess", $variables);

        if ($this->processEngineConfiguration->getHistory() != ProcessEngineConfiguration::HISTORY_NONE) {
            $this->assertEquals(count($flights), $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookFlight")->count());
        }

        // php delegates should be invoked for each element in collection
        $this->assertEquals($flights, BookFlightService::$bookedFlights);
        $this->assertEquals($flights, CancelFlightService::$canceledFlights);

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationTriggeredByEventSubProcessActivityRef.bpmn20.xml"])]
    public function testCompensateActivityRefTriggeredByEventSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");
        $this->testRule->assertProcessEnded($processInstance->getId());

        $historicVariableInstanceQuery = $this->historyService->createHistoricVariableInstanceQuery()
            ->processInstanceId($processInstance->getId())->variableName("undoBookHotel");

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() >= ProcessEngineConfigurationImpl::HISTORYLEVEL_AUDIT) {
            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals("undoBookHotel", $historicVariableInstanceQuery->list()[0]->getVariableName());
            $this->assertEquals(5, $historicVariableInstanceQuery->list()[0]->getValue());

            $this->assertEquals(0, $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookFlight")->count());
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationTriggeredByEventSubProcessInSubProcessActivityRef.bpmn20.xml"])]
    public function testCompensateActivityRefTriggeredByEventSubprocessInSubProcess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");
        $this->testRule->assertProcessEnded($processInstance->getId());

        $historicVariableInstanceQuery = $this->historyService->createHistoricVariableInstanceQuery()
            ->processInstanceId($processInstance->getId())->variableName("undoBookHotel");

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() >= ProcessEngineConfigurationImpl::HISTORYLEVEL_AUDIT) {
            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals("undoBookHotel", $historicVariableInstanceQuery->list()[0]->getVariableName());
            $this->assertEquals(5, $historicVariableInstanceQuery->list()[0]->getValue());

            $this->assertEquals(0, $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookFlight")->count());
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationInEventSubProcessActivityRef.bpmn20.xml"])]
    public function testCompensateActivityRefInEventSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");
        $this->testRule->assertProcessEnded($processInstance->getId());

        $historicVariableInstanceQuery = $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookSecondHotel");

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() >= ProcessEngineConfigurationImpl::HISTORYLEVEL_AUDIT) {
            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals("undoBookSecondHotel", $historicVariableInstanceQuery->list()[0]->getVariableName());
            $this->assertEquals(5, $historicVariableInstanceQuery->list()[0]->getValue());

            $this->assertEquals(0, $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookFlight")->count());

            $this->assertEquals(0, $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookHotel")->count());
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationInEventSubProcess.bpmn20.xml"])]
    public function testCompensateInEventSubprocess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");
        $this->testRule->assertProcessEnded($processInstance->getId());

        $historicVariableInstanceQuery = $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookSecondHotel");

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() >= ProcessEngineConfigurationImpl::HISTORYLEVEL_AUDIT) {
            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals("undoBookSecondHotel", $historicVariableInstanceQuery->list()[0]->getVariableName());
            $this->assertEquals(5, $historicVariableInstanceQuery->list()[0]->getValue());

            $historicVariableInstanceQuery = $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookFlight");

            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals(5, $historicVariableInstanceQuery->list()[0]->getValue());

            $historicVariableInstanceQuery = $this->historyService->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("undoBookHotel");

            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals(5, $historicVariableInstanceQuery->list()[0]->getValue());
        }
    }

    //@TODO - requires DSL
    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testExecutionListeners.bpmn20.xml"])]
    private function testExecutionListeners(): void
    {
        $variables = [];
        $variables["start"] = 0;
        $variables["end"] = 0;

        $processInstance = $this->runtimeService->startProcessInstanceByKey("testProcess", $variables);

        $started = $this->runtimeService->getVariable($processInstance->getId(), "start");
        $this->assertEquals(5, $started);

        $ended = $this->runtimeService->getVariable($processInstance->getId(), "end");
        $this->assertEquals(5, $ended);

        $historyLevel = $this->processEngineConfiguration->getHistoryLevel()->getId();
        if ($historyLevel > ProcessEngineConfigurationImpl::HISTORYLEVEL_NONE) {
            $finishedCount = $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookHotel")->finished()->count();
            $this->assertEquals(5, $finishedCount);
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testConcurrentExecutionsAndPendingCompensation.bpmn20.xml"])]
    public function testConcurrentExecutionsAndPendingCompensation(): void
    {
        // given
        $instance = $this->runtimeService->startProcessInstanceByKey("process");
        $processInstanceId = $instance->getId();
        $taskId = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->taskDefinitionKey("innerTask")->singleResult()->getId();

        // when (3)
        $taskId = $this->taskService->createTaskQuery()->processInstanceId($instance->getId())->taskDefinitionKey("task2")->singleResult()->getId();
        $this->taskService->complete($taskId);

        // then (3)
        $this->testRule->assertProcessEnded($processInstanceId);
        $this->assertTrue(true);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationEndEventWithScope.bpmn20.xml"])]
    public function testCompensationEndEventWithScope(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        if ($this->processEngineConfiguration->getHistory() != ProcessEngineConfiguration::HISTORY_NONE) {
            $this->assertEquals(5, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookHotel")->count());
            $this->assertEquals(5, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookFlight")->count());
        }

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationEndEventWithActivityRef.bpmn20.xml"])]
    public function testCompensationEndEventWithActivityRef(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        if ($this->processEngineConfiguration->getHistory() != ProcessEngineConfiguration::HISTORY_NONE) {
            $this->assertEquals(5, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookHotel")->count());
            $this->assertEquals(0, $this->historyService->createHistoricActivityInstanceQuery()->processInstanceId($processInstance->getId())->activityId("undoBookFlight")->count());
        }

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.activityWithCompensationEndEvent.bpmn20.xml"])]
    public function testCancelProcessInstanceWithActiveCompensation(): void
    {
        // given
        $processInstance = $this->runtimeService->startProcessInstanceByKey("compensateProcess");

        // when
        $this->runtimeService->deleteProcessInstance($processInstance->getId(), null);

        // then
        $this->testRule->assertProcessEnded($processInstance->getId());

        $this->assertTrue(true);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationEventSubProcess.bpmn20.xml"])]
    public function testCompensationEventSubProcessWithScope(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("bookingProcess")->getId();

        $this->completeTask($processInstanceId, "Book Flight");
        $this->completeTask($processInstanceId, "Book Hotel");

        // throw compensation event for current scope (without activityRef)
        $this->completeTaskWithVariable($processInstanceId, "Validate Booking", "valid", false);

        // first - compensate book flight
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());
        $this->completeTask($processInstanceId, "Cancel Flight");
        // second - compensate book hotel
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());
        $this->completeTask($processInstanceId, "Cancel Hotel");
        // third - additional compensation handler
        $this->completeTask($processInstanceId, "Update Customer Record");
        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationEventSubProcessWithActivityRef.bpmn20.xml"])]
    public function testCompensationEventSubProcessWithActivityRef(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("bookingProcess")->getId();

        $this->completeTask($processInstanceId, "Book Hotel");
        $this->completeTask($processInstanceId, "Book Flight");

        // throw compensation event for specific scope (with activityRef = subprocess)
        $this->completeTaskWithVariable($processInstanceId, "Validate Booking", "valid", false);

        // compensate the activity within this scope
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->count());
        $this->completeTask($processInstanceId, "Cancel Hotel");

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateMiSubprocessWithCompensationEventSubProcess.bpmn20.xml"])]
    public function testCompensateMiSubprocessWithCompensationEventSubProcess(): void
    {
        $variables = [];
        // multi instance collection
        $variables["flights"] = ["STS-14", "STS-28"];

        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("bookingProcess", $variables)->getId();

        $this->completeTask($processInstanceId, "Book Flight");
        $this->completeTask($processInstanceId, "Book Hotel");

        $this->completeTask($processInstanceId, "Book Flight");
        $this->completeTask($processInstanceId, "Book Hotel");

        // throw compensation event
        $this->completeTaskWithVariable($processInstanceId, "Validate Booking", "valid", false);

        // execute compensation handlers for each execution of the subprocess
        $this->completeTasks($processInstanceId, "Cancel Flight", 2);
        $this->completeTasks($processInstanceId, "Cancel Hotel", 2);
        $this->completeTasks($processInstanceId, "Update Customer Record", 2);

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensateParallelMiSubprocessWithCompensationEventSubProcess.bpmn20.xml"])]
    public function testCompensateParallelMiSubprocessWithCompensationEventSubProcess(): void
    {
        $variables = [];
        // multi instance collection
        $variables["flights"] = ["STS-14", "STS-28"];

        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("bookingProcess", $variables)->getId();

        $this->completeTasks($processInstanceId, "Book Flight", 2);
        $this->completeTasks($processInstanceId, "Book Hotel", 2);

        // throw compensation event
        $this->completeTaskWithVariable($processInstanceId, "Validate Booking", "valid", false);

        // execute compensation handlers for each execution of the subprocess
        $this->completeTasks($processInstanceId, "Cancel Flight", 2);
        $this->completeTasks($processInstanceId, "Cancel Hotel", 2);
        $this->completeTasks($processInstanceId, "Update Customer Record", 2);

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationEventSubprocessWithoutBoundaryEvents.bpmn20.xml"])]
    public function testCompensationEventSubprocessWithoutBoundaryEvents(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();

        $this->completeTask($processInstanceId, "Book Hotel");
        $this->completeTask($processInstanceId, "Book Flight");

        // throw compensation event
        $this->completeTask($processInstanceId, "throw compensation");

        // execute compensation handlers
        $this->completeTask($processInstanceId, "Cancel Flight");
        $this->completeTask($processInstanceId, "Cancel Hotel");

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationEventSubprocessReThrowCompensationEvent.bpmn20.xml"])]
    public function testCompensationEventSubprocessReThrowCompensationEvent(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();

        $this->completeTask($processInstanceId, "Book Hotel");
        $this->completeTask($processInstanceId, "Book Flight");

        // throw compensation event
        $this->completeTask($processInstanceId, "throw compensation");

        // execute compensation handler and re-throw compensation event
        $this->completeTask($processInstanceId, "Cancel Hotel");
        // execute compensation handler at subprocess
        $this->completeTask($processInstanceId, "Cancel Flight");

        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testCompensationEventSubprocessConsumeCompensationEvent.bpmn20.xml"])]
    public function testCompensationEventSubprocessConsumeCompensationEvent(): void
    {
        $processInstanceId = $this->runtimeService->startProcessInstanceByKey("compensateProcess")->getId();

        $this->completeTask($processInstanceId, "Book Hotel");
        $this->completeTask($processInstanceId, "Book Flight");

        // throw compensation event
        $this->completeTask($processInstanceId, "throw compensation");

        // execute compensation handler and consume compensation event
        $this->completeTask($processInstanceId, "Cancel Hotel");
        // compensation handler at subprocess (Cancel Flight) should not be executed
        $this->testRule->assertProcessEnded($processInstanceId);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testSubprocessCompensationHandler.bpmn20.xml"])]
    public function testSubprocessCompensationHandler(): void
    {
        // given a process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("subProcessCompensationHandler");

        // when throwing compensation
        $beforeCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($beforeCompensationTask->getId());

        // then the compensation handler has been activated
        // and the user task in the sub process can be successfully completed
        $subProcessTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($subProcessTask);
        $this->assertEquals("subProcessTask", $subProcessTask->getTaskDefinitionKey());

        $this->taskService->complete($subProcessTask->getId());

        // and the task following compensation can be successfully completed
        $afterCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($afterCompensationTask);
        $this->assertEquals("beforeEnd", $afterCompensationTask->getTaskDefinitionKey());

        $this->taskService->complete($afterCompensationTask->getId());

        // and the process has successfully ended
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testSubprocessCompensationHandler.bpmn20.xml"])]
    public function testSubprocessCompensationHandlerDeleteProcessInstance(): void
    {
        // given a process instance in compensation
        $processInstance = $this->runtimeService->startProcessInstanceByKey("subProcessCompensationHandler");
        $beforeCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($beforeCompensationTask->getId());

        // when deleting the process instance
        $this->runtimeService->deleteProcessInstance($processInstance->getId(), null);

        // then the process instance is ended
        $this->testRule->assertProcessEnded($processInstance->getId());

        $this->assertTrue(true);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testSubprocessCompensationHandlerWithEventSubprocess.bpmn20.xml"])]
    private function testSubprocessCompensationHandlerWithEventSubprocess(): void
    {
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        // given a process instance in compensation
        $processInstance = $this->runtimeService->startProcessInstanceByKey("subProcessCompensationHandlerWithEventSubprocess");
        $beforeCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($beforeCompensationTask->getId());

        // when the event subprocess is triggered that is defined as part of the compensation handler
        $this->runtimeService->correlateMessage("Message");

        // then activity instance tree is correct
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($task);
        $this->assertEquals("eventSubProcessTask", $task->getTaskDefinitionKey());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testReceiveTaskCompensationHandler.bpmn20.xml"])]
    private function testReceiveTaskCompensationHandler(): void
    {
        // given a process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("receiveTaskCompensationHandler");

        // when triggering compensation
        $beforeCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($beforeCompensationTask->getId());

        // then there is a message event subscription for the receive task compensation handler
        $eventSubscription = $this->runtimeService->createEventSubscriptionQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($eventSubscription);
        $this->assertEquals(EventType::message(), $eventSubscription->getEventType());

        // and triggering the message completes compensation
        $this->runtimeService->correlateMessage("Message");

        $afterCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($afterCompensationTask);
        $this->assertEquals("beforeEnd", $afterCompensationTask->getTaskDefinitionKey());

        $this->taskService->complete($afterCompensationTask->getId());

        // and the process has successfully ended
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testConcurrentScopeCompensation.bpmn20.xml"])]
    public function testConcurrentScopeCompensation(): void
    {
        // given a process instance with two concurrent tasks, one of which is waiting
        // before throwing compensation
        $processInstance = $this->runtimeService->startProcessInstanceByKey("concurrentScopeCompensation");
        $beforeCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("beforeCompensationTask")->singleResult();
        $concurrentTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("concurrentTask")->singleResult();

        // when throwing compensation such that two subprocesses are compensated
        $this->taskService->complete($beforeCompensationTask->getId());

        // then both compensation handlers have been executed
        if ($this->processEngineConfiguration->getHistoryLevel()->getId() >= ProcessEngineConfigurationImpl::HISTORYLEVEL_AUDIT) {
            $historicVariableInstanceQuery = $this->historyService
                ->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("compensateScope1Task");

            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals(1, $historicVariableInstanceQuery->list()[0]->getValue());

            $historicVariableInstanceQuery = $this->historyService
                ->createHistoricVariableInstanceQuery()->processInstanceId($processInstance->getId())->variableName("compensateScope2Task");

            $this->assertEquals(1, $historicVariableInstanceQuery->count());
            $this->assertEquals(1, $historicVariableInstanceQuery->list()[0]->getValue());
        }

        // and after completing the concurrent task, the process instance ends successfully
        $this->taskService->complete($concurrentTask->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Event/Compensate/CompensateEventTest.testLocalVariablesInEndExecutionListener.bpmn"])]
    public function testLocalVariablesInEndExecutionListener(): void
    {
        // given
        $setListener = new SetLocalVariableListener("foo", "bar");
        $readListener = new ReadLocalVariableListener("foo");

        $processInstance = $this->runtimeService->startProcessInstanceByKey(
            "process",
            ["setListener" => $setListener, "readListener" => $readListener]
        );

        $beforeCompensationTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();

        // when executing the compensation handler
        $this->taskService->complete($beforeCompensationTask->getId());

        // then the variable listener has been invoked and was able to read the variable on the end event
        $readListener = $this->runtimeService->getVariable($processInstance->getId(), "readListener");

        $this->assertEquals(1, count($readListener->getVariableEvents()));

        $event = $readListener->getVariableEvents()[0];
        $this->assertEquals("foo", $event->getVariableName());
        $this->assertEquals("bar", $event->getVariableValue());
    }

    private function completeTask(string $processInstanceId, string $taskName): void
    {
        $this->completeTasks($processInstanceId, $taskName, 1);
    }

    private function completeTasks(string $processInstanceId, string $taskName, int $times): void
    {
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->taskName($taskName)->list();

        $this->assertTrue($times <= count($tasks));

        for ($i = 0; $i < $times; $i += 1) {
            $this->taskService->complete($tasks[$i]->getId());
        }
    }

    private function completeTaskWithVariable(string $processInstanceId, string $taskName, ?string $variable, $value): void
    {
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstanceId)->taskName($taskName)->singleResult();
        $this->assertNotNull($task);

        $variables = [];
        if ($variable != null) {
            $variables[$variable] = $value;
        }

        $this->taskService->complete($task->getId(), $variables);
    }
}
