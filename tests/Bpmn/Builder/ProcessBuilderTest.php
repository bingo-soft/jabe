<?php

namespace Tests\Bpmn\Builder;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\Instance\Extension\ErrorEventDefinitionInterface as ExtensionErrorEventDefinitionInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    ExecutionListenerInterface,
    FailedJobRetryTimeCycleInterface,
    FormDataInterface,
    InInterface,
    InputOutputInterface,
    OutInterface,
    TaskListenerInterface
};
use BpmPlatform\Model\Bpmn\Instance\{
    AssociationInterface,
    BaseElementInterface,
    CompensateEventDefinitionInterface,
    ConditionalEventDefinitionInterface,
    ErrorInterface,
    ErrorEventDefinitionInterface,
    EscalationEventDefinitionInterface,
    EscalationInterface,
    EventInterface,
    EventDefinitionInterface,
    GatewayInterface,
    MessageEventDefinitionInterface,
    MessageInterface,
    MultiInstanceLoopCharacteristicsInterface,
    ProcessInterface,
    SignalEventDefinitionInterface,
    SignalInterface,
    SubProcessInterface,
    TaskInterface,
    TimerEventDefinitionInterface,
    InclusiveGatewayInterface
};

class ProcessBuilderTest extends TestCase
{
    public const TIMER_DATE = "2011-03-11T12:13:14Z";
    public const TIMER_DURATION = "P10D";
    public const TIMER_CYCLE = "R3/PT10H";

    public const FAILED_JOB_RETRY_TIME_CYCLE = "R5/PT1M";

    private $modelInstance;
    private $taskType;
    private $gatewayType;
    private $eventType;
    private $processType;

    protected function setUp(): void
    {
        $model = Bpmn::getInstance()->createEmptyModel()->getModel();
        $this->taskType = $model->getType(TaskInterface::class);
        $this->gatewayType = $model->getType(GatewayInterface::class);
        $this->eventType = $model->getType(EventInterface::class);
        $this->processType = $model->getType(ProcessInterface::class);
    }

    public function testCreateEmptyProcess(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()->done();

        $definitions = $this->modelInstance->getDefinitions();
        $this->assertFalse($definitions == null);
        $this->assertEquals(BpmnModelConstants::BPMN20_NS, $definitions->getTargetNamespace());

        $processes = $this->modelInstance->getModelElementsByType($this->processType);
        $this->assertCount(1, $processes);

        $process = $processes[0];
        $this->assertFalse($process->getId() == null);
    }

    public function testGetElement(): void
    {
        // Make sure this method is publicly available
        $process = Bpmn::getInstance()->createProcess()->getElement();
        $this->assertFalse($process == null);
    }

    public function testCreateProcessWithStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->done();

        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->eventType));
    }

    public function testCreateProcessWithEndEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
    }

    public function testCreateProcessWithServiceTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->serviceTask()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateProcessWithSendTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->sendTask()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateProcessWithUserTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->userTask()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateProcessWithBusinessRuleTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->businessRuleTask()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateProcessWithScriptTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->scriptTask()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateProcessWithReceiveTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->receiveTask()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateProcessWithManualTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->manualTask()
        ->endEvent()
        ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateProcessWithParallelGateway(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
          ->parallelGateway()
          ->scriptTask()
        ->endEvent()
          ->moveToLastGateway()
          ->userTask()
        ->endEvent()
        ->done();

        $this->assertCount(3, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->taskType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->gatewayType));
    }

    public function testCreateProcessWithExclusiveGateway(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask()
          ->exclusiveGateway()
            ->condition("approved", '${approved}')
            ->serviceTask()
            ->endEvent()
          ->moveToLastGateway()
            ->condition("not approved", '${!approved}')
            ->scriptTask()
            ->endEvent()
          ->done();

        $this->assertCount(3, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(3, $this->modelInstance->getModelElementsByType($this->taskType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->gatewayType));
    }

    public function testCreateProcessWithInclusiveGateway(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask()
          ->inclusiveGateway()
            ->condition("approved", '${approved}')
            ->serviceTask()
            ->endEvent()
          ->moveToLastGateway()
            ->condition("not approved", '${!approved}')
            ->scriptTask()
            ->endEvent()
          ->done();

        $inclusiveGwType = $this->modelInstance->getModel()->getType(InclusiveGatewayInterface::class);

        $this->assertCount(3, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(3, $this->modelInstance->getModelElementsByType($this->taskType));
        $this->assertCount(1, $this->modelInstance->getModelElementsByType($this->gatewayType));
    }

    public function testCreateProcessWithForkAndJoin(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask()
          ->parallelGateway()
            ->serviceTask()
            ->parallelGateway()
            ->id("join")
          ->moveToLastGateway()
            ->scriptTask()
          ->connectTo("join")
          ->userTask()
          ->endEvent()
          ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(4, $this->modelInstance->getModelElementsByType($this->taskType));
        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->gatewayType));
    }

    public function testCreateProcessWithMultipleParallelTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->parallelGateway("fork")
            ->userTask()
            ->parallelGateway("join")
          ->moveToNode("fork")
            ->serviceTask()
            ->connectTo("join")
          ->moveToNode("fork")
            ->userTask()
            ->connectTo("join")
          ->moveToNode("fork")
            ->scriptTask()
            ->connectTo("join")
          ->endEvent()
          ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->eventType));
        $this->assertCount(4, $this->modelInstance->getModelElementsByType($this->taskType));
        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->gatewayType));
    }

    public function testBaseElementDocumentation(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess("process")
                ->documentation("processDocumentation")
                ->startEvent("startEvent")
                ->documentation("startEventDocumentation_1")
                ->documentation("startEventDocumentation_2")
                ->documentation("startEventDocumentation_3")
                ->userTask("task")
                ->documentation("taskDocumentation")
                ->businessRuleTask("businessruletask")
                ->subProcess("subprocess")
                ->documentation("subProcessDocumentation")
                ->embeddedSubProcess()
                ->startEvent("subprocessStartEvent")
                ->endEvent("subprocessEndEvent")
                ->subProcessDone()
                ->endEvent("endEvent")
                ->documentation("endEventDocumentation")
                ->done();

        $this->assertEquals(
            "processDocumentation",
            $this->modelInstance->getModelElementById("process")->getDocumentations()[0]->getTextContent()
        );
        $this->assertEquals(
            "taskDocumentation",
            $this->modelInstance->getModelElementById("task")->getDocumentations()[0]->getTextContent()
        );
        $this->assertEquals(
            "subProcessDocumentation",
            $this->modelInstance->getModelElementById("subprocess")->getDocumentations()[0]->getTextContent()
        );
        $this->assertEquals(
            "endEventDocumentation",
            $this->modelInstance->getModelElementById("endEvent")->getDocumentations()[0]->getTextContent()
        );

        $startEventDocumentations = $this->modelInstance->getModelElementById("startEvent")->getDocumentations();
        $this->assertCount(3, $startEventDocumentations);
        for ($i = 1; $i <= 3; $i++) {
            $this->assertEquals("startEventDocumentation_$i", $startEventDocumentations[$i - 1]->getTextContent());
        }
    }

    public function testExtend(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask()
            ->id("task1")
          ->serviceTask()
          ->endEvent()
          ->done();

        $this->assertCount(2, $this->modelInstance->getModelElementsByType($this->taskType));

        $userTask = $this->modelInstance->getModelElementById("task1");
        $outgoingSequenceFlow = $userTask->getOutgoing()[0];

        $serviceTask = $outgoingSequenceFlow->getTarget();

        $userTask->removeOutgoing($outgoingSequenceFlow);
        $userTask->builder()
        ->scriptTask()
        ->userTask()
        ->connectTo($serviceTask->getId());

        $this->assertCount(4, $this->modelInstance->getModelElementsByType($this->taskType));
    }

    public function testCreateInvoiceProcess(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->executable()
        ->startEvent()
          ->name("Invoice received")
          ->formKey("embedded:app:forms/start-form.html")
        ->userTask()
          ->name("Assign Approver")
          ->formKey("embedded:app:forms/assign-approver.html")
          ->assignee("demo")
        ->userTask("approveInvoice")
          ->name("Approve Invoice")
          ->formKey("embedded:app:forms/approve-invoice.html")
          ->assignee('${approver}')
        ->exclusiveGateway()
          ->name("Invoice approved?")
          ->gatewayDirection("Diverging")
        ->condition("yes", '${approved}')
        ->userTask()
          ->name("Prepare Bank Transfer")
          ->formKey("embedded:app:forms/prepare-bank-transfer.html")
          ->candidateGroups("accounting")
        ->serviceTask()
          ->name("Archive Invoice")
          ->setClass("org.camunda.bpm.example.invoice.service.ArchiveInvoiceService")
        ->endEvent()
          ->name("Invoice processed")
        ->moveToLastGateway()
        ->condition("no", '${!approved}')
        ->userTask()
          ->name("Review Invoice")
          ->formKey("embedded:app:forms/review-invoice.html" )
          ->assignee("demo")
         ->exclusiveGateway()
          ->name("Review successful?")
          ->gatewayDirection("Diverging")
        ->condition("no", '${!clarified}')
        ->endEvent()
          ->name("Invoice not processed")
        ->moveToLastGateway()
        ->condition("yes", '${clarified}')
        ->connectTo("approveInvoice")
        ->done();
        $this->assertTrue(true);
    }

    public function testProcessCamundaExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess(BpmnTestConstants::PROCESS_ID)
          ->jobPriority('${somePriority}')
          ->taskPriority(BpmnTestConstants::TEST_PROCESS_TASK_PRIORITY)
          ->historyTimeToLive(BpmnTestConstants::TEST_HISTORY_TIME_TO_LIVE)
          ->startableInTasklist(BpmnTestConstants::TEST_STARTABLE_IN_TASKLIST)
          ->versionTag(BpmnTestConstants::TEST_VERSION_TAG)
          ->startEvent()
          ->endEvent()
          ->done();

        $process = $this->modelInstance->getModelElementById(BpmnTestConstants::PROCESS_ID);
        $this->assertEquals('${somePriority}', $process->getJobPriority());
        $this->assertEquals(BpmnTestConstants::TEST_PROCESS_TASK_PRIORITY, $process->getTaskPriority());
        $this->assertEquals(BpmnTestConstants::TEST_HISTORY_TIME_TO_LIVE, $process->getHistoryTimeToLive());
        $this->assertEquals(BpmnTestConstants::TEST_STARTABLE_IN_TASKLIST, $process->isStartableInTasklist());
        $this->assertEquals(BpmnTestConstants::TEST_VERSION_TAG, $process->getVersionTag());
    }

    public function testProcessStartableInTasklist(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess(BpmnTestConstants::PROCESS_ID)
        ->startEvent()
        ->endEvent()
        ->done();

        $process = $this->modelInstance->getModelElementById(BpmnTestConstants::PROCESS_ID);
        $this->assertTrue($process->isStartableInTasklist());
    }

    public function testTaskExternalTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->serviceTask(BpmnTestConstants::EXTERNAL_TASK_ID)
        ->externalTask(BpmnTestConstants::TEST_EXTERNAL_TASK_TOPIC)
        ->endEvent()
        ->done();

        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::EXTERNAL_TASK_ID);
        $this->assertEquals("external", $serviceTask->getType());
        $this->assertEquals(BpmnTestConstants::TEST_EXTERNAL_TASK_TOPIC, $serviceTask->getTopic());
    }

    public function testTaskExternalTaskErrorEventDefinition(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->serviceTask(BpmnTestConstants::EXTERNAL_TASK_ID)
        ->externalTask(BpmnTestConstants::TEST_EXTERNAL_TASK_TOPIC)
        ->errorEventDefinition()->id("id")->error(
            "myErrorCode",
            "errorMessage"
        )->expression("expression")->errorEventDefinitionDone()
        ->endEvent()
        ->moveToActivity(BpmnTestConstants::EXTERNAL_TASK_ID)
        ->boundaryEvent("boundary")->error("myErrorCode", "errorMessage")
        ->endEvent("boundaryEnd")
        ->done();

        $externalTask = $this->modelInstance->getModelElementById(BpmnTestConstants::EXTERNAL_TASK_ID);
        $extensionElements = $externalTask->getExtensionElements();
        $errorEventDefinitions = $extensionElements->getChildElementsByType(
            ExtensionErrorEventDefinitionInterface::class
        );
        $this->assertCount(1, $errorEventDefinitions);
        $errorEventDefinition = $errorEventDefinitions[0];
        $this->assertFalse($errorEventDefinition == null);
        $this->assertEquals("id", $errorEventDefinition->getId());
        $this->assertEquals("expression", $errorEventDefinition->getExpression());
        $this->assertErrorEventDefinition("boundary", "myErrorCode", "errorMessage");
    }

    protected function assertErrorEventDefinition(
        string $elementId,
        string $errorCode,
        ?string $errorMessage
    ): ErrorInterface {
        $errorEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            ErrorEventDefinitionInterface::class
        );
        $error = $errorEventDefinition->getError();
        $this->assertFalse($error == null);
        $this->assertEquals($errorCode, $error->getErrorCode());
        $this->assertEquals($errorMessage, $error->getErrorMessage());
        return $error;
    }

    protected function assertAndGetSingleEventDefinition(
        string $elementId,
        string $eventDefinitionType
    ): EventDefinitionInterface {
        $element = $this->modelInstance->getModelElementById($elementId);
        $this->assertFalse($element == null);
        $eventDefinitions = $element->getChildElementsByType(EventDefinitionInterface::class);
        $this->assertCount(1, $eventDefinitions);

        $eventDefinition = $eventDefinitions[0];
        $this->assertTrue(is_subclass_of($eventDefinition, $eventDefinitionType));
        return $eventDefinition;
    }

    public function testTaskExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->serviceTask(BpmnTestConstants::TASK_ID)
          ->asyncBefore()
          ->notExclusive()
          ->jobPriority('${somePriority}')
          ->taskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY)
          ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
        ->endEvent()
        ->done();

        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertTrue($serviceTask->isAsyncBefore());
        $this->assertFalse($serviceTask->isExclusive());
        $this->assertEquals('${somePriority}', $serviceTask->getJobPriority());
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $serviceTask->getTaskPriority());

        $this->assertFailedJobRetryTimeCycle($serviceTask);
    }

    protected function assertFailedJobRetryTimeCycle(BaseElementInterface $element): void
    {
        $this->assertFalse(empty($element->getExtensionElements()));

        $failedJobRetryTimeCycle = $element->getExtensionElements()->getElementsQuery()->filterByType(
            FailedJobRetryTimeCycleInterface::class
        )->singleResult();
        $this->assertFalse($failedJobRetryTimeCycle == null);
        $this->assertEquals(self::FAILED_JOB_RETRY_TIME_CYCLE, $failedJobRetryTimeCycle->getTextContent());
    }

    public function testServiceTaskExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->serviceTask(BpmnTestConstants::TASK_ID)
          ->setClass(BpmnTestConstants::TEST_CLASS_API)
          ->delegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API)
          ->expression(BpmnTestConstants::TEST_EXPRESSION_API)
          ->resultVariable(BpmnTestConstants::TEST_STRING_API)
          ->topic(BpmnTestConstants::TEST_STRING_API)
          ->type(BpmnTestConstants::TEST_STRING_API)
          ->taskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY)
          ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
        ->done();

        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $serviceTask->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API, $serviceTask->getDelegateExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $serviceTask->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $serviceTask->getResultVariable());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $serviceTask->getTopic());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $serviceTask->getType());
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $serviceTask->getTaskPriority());

        $this->assertFailedJobRetryTimeCycle($serviceTask);
    }

    public function testServiceTaskClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->serviceTask(BpmnTestConstants::TASK_ID)
          ->setClass(__CLASS__)
        ->done();

        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(__CLASS__, $serviceTask->getClass());
    }

    public function testSendTaskExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->sendTask(BpmnTestConstants::TASK_ID)
          ->setClass(BpmnTestConstants::TEST_CLASS_API)
          ->delegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API)
          ->expression(BpmnTestConstants::TEST_EXPRESSION_API)
          ->resultVariable(BpmnTestConstants::TEST_STRING_API)
          ->topic(BpmnTestConstants::TEST_STRING_API)
          ->type(BpmnTestConstants::TEST_STRING_API)
          ->taskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY)
          ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
        ->endEvent()
        ->done();

        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $serviceTask->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API, $serviceTask->getDelegateExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $serviceTask->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $serviceTask->getResultVariable());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $serviceTask->getTopic());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $serviceTask->getType());
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $serviceTask->getTaskPriority());

        $this->assertFailedJobRetryTimeCycle($serviceTask);
    }

    public function testSendTaskCamundaClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->sendTask(BpmnTestConstants::TASK_ID)
          ->setClass(__CLASS__)
        ->endEvent()
        ->done();

        $sendTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(__CLASS__, $sendTask->getClass());
    }

    public function testUserTaskExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->userTask(BpmnTestConstants::TASK_ID)
          ->assignee(BpmnTestConstants::TEST_STRING_API)
          ->candidateGroups(BpmnTestConstants::TEST_GROUPS_API)
          ->candidateUsers(BpmnTestConstants::TEST_USERS_LIST_API)
          ->dueDate(BpmnTestConstants::TEST_DUE_DATE_API)
          ->followUpDate(BpmnTestConstants::TEST_FOLLOW_UP_DATE_API)
          ->formHandlerClass(BpmnTestConstants::TEST_CLASS_API)
          ->formKey(BpmnTestConstants::TEST_STRING_API)
          ->priority(BpmnTestConstants::TEST_PRIORITY_API)
          ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
        ->endEvent()
        ->done();

        $userTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $userTask->getAssignee());
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_API, $userTask->getCandidateGroups());
        $this->assertTrue($userTask->getCandidateGroupsList() == BpmnTestConstants::TEST_GROUPS_LIST_API);
        $this->assertEquals(BpmnTestConstants::TEST_USERS_API, $userTask->getCandidateUsers());
        $this->assertTrue($userTask->getCandidateUsersList() == BpmnTestConstants::TEST_USERS_LIST_API);
        $this->assertEquals(BpmnTestConstants::TEST_DUE_DATE_API, $userTask->getDueDate());
        $this->assertEquals(BpmnTestConstants::TEST_FOLLOW_UP_DATE_API, $userTask->getFollowUpDate());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $userTask->getFormHandlerClass());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $userTask->getFormKey());
        $this->assertEquals(BpmnTestConstants::TEST_PRIORITY_API, $userTask->getPriority());

        $this->assertFailedJobRetryTimeCycle($userTask);
    }

    public function testBusinessRuleTaskExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->businessRuleTask(BpmnTestConstants::TASK_ID)
            ->setClass(BpmnTestConstants::TEST_CLASS_API)
            ->delegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API)
            ->expression(BpmnTestConstants::TEST_EXPRESSION_API)
            ->resultVariable("resultVar")
            ->topic("topic")
            ->type("type")
            ->decisionRef("decisionRef")
            ->decisionRefBinding("latest")
            ->decisionRefVersion("7")
            ->decisionRefVersionTag("0.1.0")
            ->decisionRefTenantId("tenantId")
            ->mapDecisionResult("singleEntry")
            ->taskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY)
            ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
          ->endEvent()
          ->done();

        $businessRuleTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $businessRuleTask->getClass());
        $this->assertEquals(
            BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API,
            $businessRuleTask->getDelegateExpression()
        );
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $businessRuleTask->getExpression());
        $this->assertEquals("resultVar", $businessRuleTask->getResultVariable());
        $this->assertEquals("topic", $businessRuleTask->getTopic());
        $this->assertEquals("type", $businessRuleTask->getType());
        $this->assertEquals("decisionRef", $businessRuleTask->getDecisionRef());
        $this->assertEquals("latest", $businessRuleTask->getDecisionRefBinding());
        $this->assertEquals("7", $businessRuleTask->getDecisionRefVersion());
        $this->assertEquals("0.1.0", $businessRuleTask->getDecisionRefVersionTag());
        $this->assertEquals("tenantId", $businessRuleTask->getDecisionRefTenantId());
        $this->assertEquals("singleEntry", $businessRuleTask->getMapDecisionResult());
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $businessRuleTask->getTaskPriority());

        $this->assertFailedJobRetryTimeCycle($businessRuleTask);
    }

    public function testBusinessRuleTaskClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->businessRuleTask(BpmnTestConstants::TASK_ID)
          ->setClass(Bpmn::class)
        ->endEvent()
        ->done();

        $businessRuleTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(Bpmn::class, $businessRuleTask->getClass());
    }

    public function testScriptTaskExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->scriptTask(BpmnTestConstants::TASK_ID)
            ->resultVariable(BpmnTestConstants::TEST_STRING_API)
            ->resource(BpmnTestConstants::TEST_STRING_API)
            ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
          ->endEvent()
          ->done();

        $scriptTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $scriptTask->getResultVariable());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $scriptTask->getResource());

        $this->assertFailedJobRetryTimeCycle($scriptTask);
    }

    public function testStartEventExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent(BpmnTestConstants::START_EVENT_ID)
          ->asyncBefore()
          ->exclusive()
          ->formHandlerClass(BpmnTestConstants::TEST_CLASS_API)
          ->formKey(BpmnTestConstants::TEST_STRING_API)
          ->initiator(BpmnTestConstants::TEST_STRING_API)
          ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
        ->done();

        $startEvent = $this->modelInstance->getModelElementById(BpmnTestConstants::START_EVENT_ID);
        $this->assertTrue($startEvent->isAsyncBefore());
        $this->assertFalse($startEvent->isExclusive());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $startEvent->getFormHandlerClass());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $startEvent->getFormKey());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $startEvent->getInitiator());

        $this->assertFailedJobRetryTimeCycle($startEvent);
    }

    public function testErrorDefinitionsForStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent("start")
          ->errorEventDefinition("event")
            ->errorCodeVariable("errorCodeVariable")
            ->errorMessageVariable("errorMessageVariable")
            ->error("errorCode", "errorMessage")
          ->errorEventDefinitionDone()
        ->endEvent()
        ->done();

        $this->assertErrorEventDefinition("start", "errorCode", "errorMessage");
        $this->assertErrorEventDefinitionForErrorVariables("start", "errorCodeVariable", "errorMessageVariable");
    }

    protected function assertErrorEventDefinitionForErrorVariables(
        string $elementId,
        string $errorCodeVariable,
        string $errorMessageVariable
    ): void {
        $errorEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            ErrorEventDefinitionInterface::class
        );
        $this->assertFalse($errorEventDefinition == null);
        if ($errorCodeVariable != null) {
            $this->assertEquals($errorCodeVariable, $errorEventDefinition->getErrorCodeVariable());
        }
        if ($errorMessageVariable != null) {
            $this->assertEquals($errorMessageVariable, $errorEventDefinition->getErrorMessageVariable());
        }
    }

    public function testErrorDefinitionsForStartEventWithoutEventDefinitionId(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent("start")
          ->errorEventDefinition()
            ->errorCodeVariable("errorCodeVariable")
            ->errorMessageVariable("errorMessageVariable")
            ->error("errorCode", "errorMessage")
          ->errorEventDefinitionDone()
        ->endEvent()->done();

        $this->assertErrorEventDefinition("start", "errorCode", "errorMessage");
        $this->assertErrorEventDefinitionForErrorVariables("start", "errorCodeVariable", "errorMessageVariable");
    }

    public function testCallActivityCamundaExtension(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->callActivity(BpmnTestConstants::CALL_ACTIVITY_ID)
            ->calledElement(BpmnTestConstants::TEST_STRING_API)
            ->asyncBefore()
            ->calledElementBinding("version")
            ->calledElementVersion("1.0")
            ->calledElementVersionTag("ver-1.0")
            ->calledElementTenantId("t1")
            ->caseRef("case")
            ->caseBinding("deployment")
            ->caseVersion("2")
            ->caseTenantId("t2")
            ->in("in-source", "in-target")
            ->out("out-source", "out-target")
            ->variableMappingClass(BpmnTestConstants::TEST_CLASS_API)
            ->variableMappingDelegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API)
            ->notExclusive()
            ->failedJobRetryTimeCycle(self::FAILED_JOB_RETRY_TIME_CYCLE)
          ->endEvent()
          ->done();

        $callActivity = $this->modelInstance->getModelElementById(BpmnTestConstants::CALL_ACTIVITY_ID);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $callActivity->getCalledElement());
        $this->assertTrue($callActivity->isAsyncBefore());
        $this->assertEquals("version", $callActivity->getCalledElementBinding());
        $this->assertEquals("1.0", $callActivity->getCalledElementVersion());
        $this->assertEquals("ver-1.0", $callActivity->getCalledElementVersionTag());
        $this->assertEquals("t1", $callActivity->getCalledElementTenantId());
        $this->assertEquals("case", $callActivity->getCaseRef());
        $this->assertEquals("deployment", $callActivity->getCaseBinding());
        $this->assertEquals("2", $callActivity->getCaseVersion());
        $this->assertEquals("t2", $callActivity->getCaseTenantId());
        $this->assertFalse($callActivity->isExclusive());

        $in = $callActivity->getExtensionElements()->getUniqueChildElementByType(InInterface::class);
        $this->assertEquals("in-source", $in->getSource());
        $this->assertEquals("in-target", $in->getTarget());

        $out = $callActivity->getExtensionElements()->getUniqueChildElementByType(OutInterface::class);
        $this->assertEquals("out-source", $out->getSource());
        $this->assertEquals("out-target", $out->getTarget());

        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $callActivity->getVariableMappingClass());
        $this->assertEquals(
            BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API,
            $callActivity->getVariableMappingDelegateExpression()
        );
        $this->assertFailedJobRetryTimeCycle($callActivity);
    }

    public function testCallActivityVariableMappingClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->callActivity(BpmnTestConstants::CALL_ACTIVITY_ID)
          ->variableMappingClass(__CLASS__)
        ->endEvent()
        ->done();

        $callActivity = $this->modelInstance->getModelElementById(BpmnTestConstants::CALL_ACTIVITY_ID);
        $this->assertEquals(__CLASS__, $callActivity->getVariableMappingClass());
    }

    public function testSubProcessBuilder(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
          ->asyncBefore()
          ->embeddedSubProcess()
            ->startEvent()
            ->userTask()
            ->endEvent()
          ->subProcessDone()
          ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
          ->endEvent()
          ->done();

        $subProcess = $this->modelInstance->getModelElementById(BpmnTestConstants::SUB_PROCESS_ID);
        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID);
        $this->assertTrue($subProcess->isAsyncBefore());
        $this->assertTrue($subProcess->isExclusive());
        $this->assertCount(2, $subProcess->getChildElementsByType(EventInterface::class));
        $this->assertCount(1, $subProcess->getChildElementsByType(TaskInterface::class));
        $this->assertCount(5, $subProcess->getFlowElements());
        $this->assertTrue($subProcess->getSucceedingNodes()->singleResult()->equals($serviceTask));
    }

    public function testSubProcessBuilderDetached(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
          ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
          ->endEvent()
          ->done();

        $subProcess = $this->modelInstance->getModelElementById(BpmnTestConstants::SUB_PROCESS_ID);

        $subProcess->builder()
          ->asyncBefore()
          ->embeddedSubProcess()
            ->startEvent()
            ->userTask()
            ->endEvent();

        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID);
        $this->assertTrue($subProcess->isAsyncBefore());
        $this->assertTrue($subProcess->isExclusive());
        $this->assertCount(2, $subProcess->getChildElementsByType(EventInterface::class));
        $this->assertCount(1, $subProcess->getChildElementsByType(TaskInterface::class));
        $this->assertCount(5, $subProcess->getFlowElements());
        $this->assertTrue($subProcess->getSucceedingNodes()->singleResult()->equals($serviceTask));
    }

    public function testSubProcessBuilderNested(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->subProcess(BpmnTestConstants::SUB_PROCESS_ID . '1')
          ->asyncBefore()
          ->embeddedSubProcess()
            ->startEvent()
            ->userTask()
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID . '2')
              ->asyncBefore()
              ->notExclusive()
              ->embeddedSubProcess()
                ->startEvent()
                ->userTask()
                ->endEvent()
              ->subProcessDone()
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID . '1')
            ->endEvent()
          ->subProcessDone()
        ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID . '2')
        ->endEvent()
        ->done();

        $subProcess = $this->modelInstance->getModelElementById(BpmnTestConstants::SUB_PROCESS_ID . '1');
        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID . '2');
        $this->assertTrue($subProcess->isAsyncBefore());
        $this->assertTrue($subProcess->isExclusive());
        $this->assertCount(2, $subProcess->getChildElementsByType(EventInterface::class));
        $this->assertCount(2, $subProcess->getChildElementsByType(TaskInterface::class));
        $this->assertCount(1, $subProcess->getChildElementsByType(SubProcessInterface::class));
        $this->assertCount(9, $subProcess->getFlowElements());
        $this->assertTrue($subProcess->getSucceedingNodes()->singleResult()->equals($serviceTask));

        $nestedSubProcess = $this->modelInstance->getModelElementById(BpmnTestConstants::SUB_PROCESS_ID . '2');
        $nestedServiceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID . '1');
        $this->assertTrue($nestedSubProcess->isAsyncBefore());
        $this->assertFalse($nestedSubProcess->isExclusive());
        $this->assertCount(2, $nestedSubProcess->getChildElementsByType(EventInterface::class));
        $this->assertCount(1, $nestedSubProcess->getChildElementsByType(TaskInterface::class));
        $this->assertCount(5, $nestedSubProcess->getFlowElements());
        $this->assertTrue($nestedSubProcess->getSucceedingNodes()->singleResult()->equals($nestedServiceTask));
    }

    public function testSubProcessBuilderWrongScope(): void
    {
        $this->expectException(BpmnModelException::class);
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->subProcessDone()
          ->endEvent()
          ->done();
    }

    public function testTransactionBuilder(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->transaction(BpmnTestConstants::TRANSACTION_ID)
          ->asyncBefore()
          ->method("##Image")
          ->embeddedSubProcess()
            ->startEvent()
            ->userTask()
            ->endEvent()
          ->transactionDone()
        ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
        ->endEvent()
        ->done();

        $transaction = $this->modelInstance->getModelElementById(BpmnTestConstants::TRANSACTION_ID);
        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID);
        $this->assertTrue($transaction->isAsyncBefore());
        $this->assertTrue($transaction->isExclusive());
        $this->assertEquals("##Image", $transaction->getMethod());
        $this->assertCount(2, $transaction->getChildElementsByType(EventInterface::class));
        $this->assertCount(1, $transaction->getChildElementsByType(TaskInterface::class));
        $this->assertCount(5, $transaction->getFlowElements());
        $this->assertTrue($transaction->getSucceedingNodes()->singleResult()->equals($serviceTask));
    }

    public function testTransactionBuilderDetached(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->transaction(BpmnTestConstants::TRANSACTION_ID)
          ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
          ->endEvent()
          ->done();

        $transaction = $this->modelInstance->getModelElementById(BpmnTestConstants::TRANSACTION_ID);

        $transaction->builder()
          ->asyncBefore()
          ->embeddedSubProcess()
            ->startEvent()
            ->userTask()
            ->endEvent();

        $serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID);
        $this->assertTrue($transaction->isAsyncBefore());
        $this->assertTrue($transaction->isExclusive());
        $this->assertCount(2, $transaction->getChildElementsByType(EventInterface::class));
        $this->assertCount(1, $transaction->getChildElementsByType(TaskInterface::class));
        $this->assertCount(5, $transaction->getFlowElements());
        $this->assertTrue($transaction->getSucceedingNodes()->singleResult()->equals($serviceTask));
    }

    public function testScriptText(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->scriptTask("script")
            ->scriptFormat("groovy")
            ->scriptText("println \"hello, world\";")
          ->endEvent()
          ->done();

        $scriptTask = $this->modelInstance->getModelElementById("script");
        $this->assertEquals("groovy", $scriptTask->getScriptFormat());
        $this->assertEquals("println \"hello, world\";", $scriptTask->getScript()->getTextContent());
    }

    public function testEventBasedGatewayAsyncAfter(): void
    {
        try {
            Bpmn::getInstance()->createProcess()
              ->startEvent()
              ->eventBasedGateway()
                ->asyncAfter()
              ->done();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        try {
            Bpmn::getInstance()->createProcess()
              ->startEvent()
              ->eventBasedGateway()
                ->asyncAfter(true)
              ->endEvent()
              ->done();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testMessageStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent("start")->message("message")
          ->done();

        $this->assertMessageEventDefinition("start", "message");
    }

    protected function assertMessageEventDefinition(string $elementId, string $messageName): MessageInterface
    {
        $messageEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            MessageEventDefinitionInterface::class
        );
        $message = $messageEventDefinition->getMessage();
        $this->assertFalse(empty($message));
        $this->assertEquals($messageName, $message->getName());

        return $message;
    }

    public function testMessageStartEventWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent("start")->message("message")
            ->subProcess()->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subStart")->message("message")
            ->subProcessDone()
          ->done();

        $message = $this->assertMessageEventDefinition("start", "message");
        $subMessage = $this->assertMessageEventDefinition("subStart", "message");

        $this->assertEquals($subMessage, $message);

        $this->assertOnlyOneMessageExists("message");
    }

    protected function assertOnlyOneMessageExists(string $messageName): void
    {
        $messages = $this->modelInstance->getModelElementsByType(MessageInterface::class);
        foreach ($messages as $message) {
            $this->assertEquals($messageName, $message->getName());
        }
    }

    public function testIntermediateMessageCatchEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent("catch")->message("message")
          ->done();

        $this->assertMessageEventDefinition("catch", "message");
    }

    public function testIntermediateMessageCatchEventWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent("catch1")->message("message")
          ->intermediateCatchEvent("catch2")->message("message")
          ->done();

        $message1 = $this->assertMessageEventDefinition("catch1", "message");
        $message2 = $this->assertMessageEventDefinition("catch2", "message");

        $this->assertTrue($message1->equals($message2));

        $this->assertOnlyOneMessageExists("message");
    }

    public function testMessageEndEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end")->message("message")
          ->done();

        $this->assertMessageEventDefinition("end", "message");
    }

    public function testMessageEventDefintionEndEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end")
          ->messageEventDefinition()
            ->message("message")
          ->done();

        $this->assertMessageEventDefinition("end", "message");
    }

    public function testMessageEndEventWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->parallelGateway()
          ->endEvent("end1")->message("message")
          ->moveToLastGateway()
          ->endEvent("end2")->message("message")
          ->done();

        $message1 = $this->assertMessageEventDefinition("end1", "message");
        $message2 = $this->assertMessageEventDefinition("end2", "message");

        $this->assertTrue($message1->equals($message2));

        $this->assertOnlyOneMessageExists("message");
    }

    public function testMessageEventDefinitionEndEventWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->parallelGateway()
        ->endEvent("end1")
        ->messageEventDefinition()
          ->message("message")
          ->messageEventDefinitionDone()
        ->moveToLastGateway()
        ->endEvent("end2")
        ->messageEventDefinition()
          ->message("message")
        ->done();

        $message1 = $this->assertMessageEventDefinition("end1", "message");
        $message2 = $this->assertMessageEventDefinition("end2", "message");

        $this->assertTrue($message1->equals($message2));

        $this->assertOnlyOneMessageExists("message");
    }

    public function testIntermediateMessageThrowEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw")->message("message")
          ->done();

        $this->assertMessageEventDefinition("throw", "message");
    }

    public function testIntermediateMessageEventDefintionThrowEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw")
          ->messageEventDefinition()
            ->message("message")
          ->done();

        $this->assertMessageEventDefinition("throw", "message");
    }

    public function testIntermediateMessageThrowEventWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw1")->message("message")
          ->intermediateThrowEvent("throw2")->message("message")
          ->done();

        $message1 = $this->assertMessageEventDefinition("throw1", "message");
        $message2 = $this->assertMessageEventDefinition("throw2", "message");

        $this->assertTrue($message1->equals($message2));

        $this->assertOnlyOneMessageExists("message");
    }

    public function testIntermediateMessageEventDefintionThrowEventWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw1")
          ->messageEventDefinition()
            ->message("message")
            ->messageEventDefinitionDone()
          ->intermediateThrowEvent("throw2")
          ->messageEventDefinition()
            ->message("message")
            ->messageEventDefinitionDone()
          ->done();

        $message1 = $this->assertMessageEventDefinition("throw1", "message");
        $message2 = $this->assertMessageEventDefinition("throw2", "message");

        $this->assertTrue($message1->equals($message2));

        $this->assertOnlyOneMessageExists("message");
    }

    public function testIntermediateMessageThrowEventWithMessageDefinition(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw1")
          ->messageEventDefinition()
            ->id("messageEventDefinition")
            ->message("message")
            ->taskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY)
            ->type("external")
            ->topic("TOPIC")
          ->done();

        $event = $this->modelInstance->getModelElementById("messageEventDefinition");
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $event->getTaskPriority());
        $this->assertEquals("TOPIC", $event->getTopic());
        $this->assertEquals("external", $event->getType());
        $this->assertEquals("message", $event->getMessage()->getName());
    }

    public function testIntermediateMessageThrowEventWithTaskPriority(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw1")
          ->messageEventDefinition("messageEventDefinition")
            ->taskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY)
          ->done();

        $event = $this->modelInstance->getModelElementById("messageEventDefinition");
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $event->getTaskPriority());
    }

    public function testEndEventWithTaskPriority(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end")
          ->messageEventDefinition("messageEventDefinition")
            ->taskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY)
          ->done();

        $event = $this->modelInstance->getModelElementById("messageEventDefinition");
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $event->getTaskPriority());
    }

    public function testMessageEventDefinitionWithID(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw1")
          ->messageEventDefinition("messageEventDefinition")
          ->done();

        $event = $this->modelInstance->getModelElementById("messageEventDefinition");
        $this->assertFalse($event == null);

        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw2")
          ->messageEventDefinition()->id("messageEventDefinition1")
          ->done();

        $event = $this->modelInstance->getModelElementById("messageEventDefinition1");
        $this->assertFalse($event == null);
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end1")
          ->messageEventDefinition("messageEventDefinition")
          ->done();

        $event = $this->modelInstance->getModelElementById("messageEventDefinition");
        $this->assertFalse($event == null);

        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end2")
          ->messageEventDefinition()->id("messageEventDefinition1")
          ->done();

        $event = $this->modelInstance->getModelElementById("messageEventDefinition1");
        $this->assertFalse($event == null);
    }

    public function testReceiveTaskMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->receiveTask("receive")->message("message")
          ->done();

        $receiveTask = $this->modelInstance->getModelElementById("receive");

        $message = $receiveTask->getMessage();
        $this->assertFalse($message == null);
        $this->assertEquals("message", $message->getName());
    }

    public function testReceiveTaskWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->receiveTask("receive1")->message("message")
          ->receiveTask("receive2")->message("message")
          ->done();

        $receiveTask1 = $this->modelInstance->getModelElementById("receive1");
        $message1 = $receiveTask1->getMessage();

        $receiveTask2 = $this->modelInstance->getModelElementById("receive2");
        $message2 = $receiveTask2->getMessage();

        $this->assertTrue($message1->equals($message2));

        $this->assertOnlyOneMessageExists("message");
    }

    public function testSendTaskMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->sendTask("send")->message("message")
          ->done();

        $sendTask = $this->modelInstance->getModelElementById("send");

        $message = $sendTask->getMessage();
        $this->assertFalse($message == null);
        $this->assertEquals("message", $message->getName());
    }

    public function testSendTaskWithExistingMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->sendTask("send1")->message("message")
          ->sendTask("send2")->message("message")
          ->done();

        $sendTask1 = $this->modelInstance->getModelElementById("send1");
        $message1 = $sendTask1->getMessage();

        $sendTask2 = $this->modelInstance->getModelElementById("send2");
        $message2 = $sendTask2->getMessage();

        $this->assertTrue($message1->equals($message2));

        $this->assertOnlyOneMessageExists("message");
    }

    public function testSignalStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent("start")->signal("signal")
          ->done();

        $this->assertSignalEventDefinition("start", "signal");
    }

    protected function assertSignalEventDefinition(string $elementId, string $signalName): SignalInterface
    {
        $signalEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            SignalEventDefinitionInterface::class
        );
        $signal = $signalEventDefinition->getSignal();
        $this->assertFalse($signal == null);
        $this->assertEquals($signalName, $signal->getName());

        return $signal;
    }

    public function testSignalStartEventWithExistingSignal(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent("start")->signal("signal")
          ->subProcess()->triggerByEvent()
          ->embeddedSubProcess()
          ->startEvent("subStart")->signal("signal")
          ->subProcessDone()
          ->done();

        $signal = $this->assertSignalEventDefinition("start", "signal");
        $subSignal = $this->assertSignalEventDefinition("subStart", "signal");

        $this->assertTrue($signal->equals($subSignal));

        $this->assertOnlyOneSignalExists("signal");
    }

    protected function assertOnlyOneSignalExists(string $signalName): void
    {
        $signals = $this->modelInstance->getModelElementsByType(SignalInterface::class);
        foreach ($signals as $signal) {
            $this->assertEquals($signalName, $signal->getName());
        }
    }

    public function testIntermediateSignalCatchEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent("catch")->signal("signal")
          ->done();

        $this->assertSignalEventDefinition("catch", "signal");
    }

    public function testIntermediateSignalCatchEventWithExistingSignal(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent("catch1")->signal("signal")
          ->intermediateCatchEvent("catch2")->signal("signal")
          ->done();

        $signal1 = $this->assertSignalEventDefinition("catch1", "signal");
        $signal2 = $this->assertSignalEventDefinition("catch2", "signal");

        $this->assertTrue($signal1->equals($signal2));

        $this->assertOnlyOneSignalExists("signal");
    }

    public function testSignalEndEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end")->signal("signal")
          ->done();

        $this->assertSignalEventDefinition("end", "signal");
    }

    public function testSignalEndEventWithExistingSignal(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->parallelGateway()
          ->endEvent("end1")->signal("signal")
          ->moveToLastGateway()
          ->endEvent("end2")->signal("signal")
          ->done();

        $signal1 = $this->assertSignalEventDefinition("end1", "signal");
        $signal2 = $this->assertSignalEventDefinition("end2", "signal");

        $this->assertTrue($signal1->equals($signal2));

        $this->assertOnlyOneSignalExists("signal");
    }

    public function testIntermediateSignalThrowEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw")->signal("signal")
          ->done();

        $this->assertSignalEventDefinition("throw", "signal");
    }

    public function testIntermediateSignalThrowEventWithExistingSignal(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw1")->signal("signal")
          ->intermediateThrowEvent("throw2")->signal("signal")
          ->done();

        $signal1 = $this->assertSignalEventDefinition("throw1", "signal");
        $signal2 = $this->assertSignalEventDefinition("throw2", "signal");

        $this->assertTrue($signal1->equals($signal2));

        $this->assertOnlyOneSignalExists("signal");
    }

    public function testIntermediateSignalThrowEventWithPayloadLocalVar(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw")
            ->signalEventDefinition("signal")
              ->inSourceTarget("source", "target1")
              ->inSourceExpressionTarget('${"sourceExpression"}', "target2")
              ->inAllVariables("all", true)
              ->inBusinessKey("aBusinessKey")
              ->throwEventDefinitionDone()
          ->endEvent()
          ->done();

        $this->assertSignalEventDefinition("throw", "signal");
        $signalEventDefinition = $this->assertAndGetSingleEventDefinition(
            "throw",
            SignalEventDefinitionInterface::class
        );

        $this->assertEquals("signal", $signalEventDefinition->getSignal()->getName());

        $inParams = $signalEventDefinition->getExtensionElements()
        ->getElementsQuery()
        ->filterByType(InInterface::class)->list();
        $this->assertCount(4, $inParams);

        $paramCounter = 0;
        foreach ($inParams as $inParam) {
            if ($inParam->getVariables() != null) {
                $this->assertEquals("all", $inParam->getVariables());
                if ($inParam->getLocal()) {
                    $paramCounter += 1;
                }
            } elseif ($inParam->getBusinessKey() != null) {
                $this->assertEquals("aBusinessKey", $inParam->getBusinessKey());
                $paramCounter += 1;
            } elseif ($inParam->getSourceExpression() != null) {
                $this->assertEquals('${"sourceExpression"}', $inParam->getSourceExpression());
                $this->assertEquals("target2", $inParam->getTarget());
                $paramCounter += 1;
            } elseif ($inParam->getSource() != null) {
                $this->assertEquals("source", $inParam->getSource());
                $this->assertEquals("target1", $inParam->getTarget());
                $paramCounter += 1;
            }
        }
        $this->assertCount($paramCounter, $inParams);
    }

    public function testIntermediateSignalThrowEventWithPayload(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw")
            ->signalEventDefinition("signal")
              ->inAllVariables("all")
              ->throwEventDefinitionDone()
          ->endEvent()
          ->done();

        $signalEventDefinition = $this->assertAndGetSingleEventDefinition(
            "throw",
            SignalEventDefinitionInterface::class
        );

        $inParams = $signalEventDefinition->getExtensionElements()->getElementsQuery()
                    ->filterByType(InInterface::class)->list();
        $this->assertCount(1, $inParams);

        $this->assertEquals("all", $inParams[0]->getVariables());
    }

    public function testMessageBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task") // jump back to user task and attach a boundary event
          ->boundaryEvent("boundary")->message("message")
          ->endEvent("boundaryEnd")
          ->done();

        $this->assertMessageEventDefinition("boundary", "message");

        $userTask = $this->modelInstance->getModelElementById("task");
        $boundaryEvent = $this->modelInstance->getModelElementById("boundary");
        $boundaryEnd = $this->modelInstance->getModelElementById("boundaryEnd");

        // boundary event is attached to the user task
        $this->assertTrue($boundaryEvent->getAttachedTo()->equals($userTask));

        // boundary event has no incoming sequence flows
        $this->assertEmpty($boundaryEvent->getIncoming());

        // the next flow node is the boundary end event
        $succeedingNodes = $boundaryEvent->getSucceedingNodes()->list();
        foreach ($succeedingNodes as $node) {
            $this->assertTrue($node->equals($boundaryEnd));
        }
    }

    public function testMultipleBoundaryEvents(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task") // jump back to user task and attach a boundary event
          ->boundaryEvent("boundary1")->message("message")
          ->endEvent("boundaryEnd1")
          ->moveToActivity("task") // jump back to user task and attach another boundary event
          ->boundaryEvent("boundary2")->signal("signal")
          ->endEvent("boundaryEnd2")
          ->done();

        $this->assertMessageEventDefinition("boundary1", "message");
        $this->assertSignalEventDefinition("boundary2", "signal");

        $userTask = $this->modelInstance->getModelElementById("task");
        $boundaryEvent1 = $this->modelInstance->getModelElementById("boundary1");
        $boundaryEnd1 = $this->modelInstance->getModelElementById("boundaryEnd1");
        $boundaryEvent2 = $this->modelInstance->getModelElementById("boundary2");
        $boundaryEnd2 = $this->modelInstance->getModelElementById("boundaryEnd2");

        // boundary events are attached to the user task
        $this->assertTrue($boundaryEvent1->getAttachedTo()->equals($userTask));
        $this->assertTrue($boundaryEvent2->getAttachedTo()->equals($userTask));

        // boundary events have no incoming sequence flows
        $this->assertEmpty($boundaryEvent1->getIncoming());
        $this->assertEmpty($boundaryEvent2->getIncoming());

        // the next flow node is the boundary end event
        $succeedingNodes = $boundaryEvent1->getSucceedingNodes()->list();
        foreach ($succeedingNodes as $node) {
            $this->assertTrue($node->equals($boundaryEnd1));
        }
        $succeedingNodes = $boundaryEvent2->getSucceedingNodes()->list();
        foreach ($succeedingNodes as $node) {
            $this->assertTrue($node->equals($boundaryEnd2));
        }
    }

    public function testTaskListenerByClassName(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerClass("start", "aClass")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("aClass", $taskListener->getClass());
        $this->assertEquals("start", $taskListener->getEvent());
    }

    public function testTaskListenerByClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
          ->userTask("task")
            ->taskListenerClass("start", __CLASS__)
        ->endEvent()
        ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals(__CLASS__, $taskListener->getClass());
        $this->assertEquals("start", $taskListener->getEvent());
    }

    public function testTaskListenerByExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerExpression("start", "anExpression")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("anExpression", $taskListener->getExpression());
        $this->assertEquals("start", $taskListener->getEvent());
    }

    public function testTaskListenerByDelegateExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerDelegateExpression("start", "aDelegate")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("aDelegate", $taskListener->getDelegateExpression());
        $this->assertEquals("start", $taskListener->getEvent());
    }

    public function testTimeoutCycleTaskListenerByClassName(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerClassTimeoutWithCycle("timeout-1", "aClass", "R/PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("aClass", $taskListener->getClass());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertFalse($timeout->getTimeCycle() == null);
        $this->assertEquals("R/PT1H", $timeout->getTimeCycle()->getRawTextContent());
        $this->assertNull($timeout->getTimeDate());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testTimeoutDateTaskListenerByClassName(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerClassTimeoutWithDate("timeout-1", "aClass", "2019-09-09T12:12:12")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("aClass", $taskListener->getClass());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertNull($timeout->getTimeCycle());
        $this->assertFalse($timeout->getTimeDate() == null);
        $this->assertEquals("2019-09-09T12:12:12", $timeout->getTimeDate()->getRawTextContent());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testTimeoutDurationTaskListenerByClassName(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerClassTimeoutWithDuration("timeout-1", "aClass", "PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];

        $taskListener = $taskListeners[0];
        $this->assertEquals("aClass", $taskListener->getClass());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDate());
        $this->assertFalse($timeout->getTimeDuration() == null);
        $this->assertEquals("PT1H", $timeout->getTimeDuration()->getRawTextContent());
    }

    public function testTimeoutDurationTaskListenerByClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerClassTimeoutWithDuration("timeout-1", __CLASS__, "PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals(__CLASS__, $taskListener->getClass());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDate());
        $this->assertFalse($timeout->getTimeDuration() == null);
        $this->assertEquals("PT1H", $timeout->getTimeDuration()->getRawTextContent());
    }

    public function testTimeoutCycleTaskListenerByClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerClassTimeoutWithCycle("timeout-1", __CLASS__, "R/PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals(__CLASS__, $taskListener->getClass());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertFalse($timeout->getTimeCycle() == null);
        $this->assertEquals("R/PT1H", $timeout->getTimeCycle()->getRawTextContent());
        $this->assertNull($timeout->getTimeDate());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testTimeoutDateTaskListenerByClass(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
            ->userTask("task")
              ->taskListenerClassTimeoutWithDate("timeout-1", __CLASS__, "2019-09-09T12:12:12")
          ->endEvent()
          ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals(__CLASS__, $taskListener->getClass());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertFalse($timeout->getTimeDate() == null);
        $this->assertEquals("2019-09-09T12:12:12", $timeout->getTimeDate()->getRawTextContent());
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testTimeoutCycleTaskListenerByExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerExpressionTimeoutWithCycle("timeout-1", "anExpression", "R/PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("anExpression", $taskListener->getExpression());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertFalse($timeout->getTimeCycle() == null);
        $this->assertEquals("R/PT1H", $timeout->getTimeCycle()->getRawTextContent());
        $this->assertNull($timeout->getTimeDate());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testTimeoutDateTaskListenerByExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerExpressionTimeoutWithDate("timeout-1", "anExpression", "2019-09-09T12:12:12")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("anExpression", $taskListener->getExpression());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertFalse($timeout->getTimeDate() == null);
        $this->assertEquals("2019-09-09T12:12:12", $timeout->getTimeDate()->getRawTextContent());
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testTimeoutDurationTaskListenerByExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerExpressionTimeoutWithDuration("timeout-1", "anExpression", "PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("anExpression", $taskListener->getExpression());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDate());
        $this->assertFalse($timeout->getTimeDuration() == null);
        $this->assertEquals("PT1H", $timeout->getTimeDuration()->getRawTextContent());
    }

    public function testTimeoutCycleTaskListenerByDelegateExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerDelegateExpressionTimeoutWithCycle("timeout-1", "aDelegate", "R/PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("aDelegate", $taskListener->getDelegateExpression());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertFalse($timeout->getTimeCycle() == null);
        $this->assertEquals("R/PT1H", $timeout->getTimeCycle()->getRawTextContent());
        $this->assertNull($timeout->getTimeDate());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testCamundaTimeoutDateTaskListenerByDelegateExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerDelegateExpressionTimeoutWithDate("timeout-1", "aDelegate", "2019-09-09T12:12:12")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("aDelegate", $taskListener->getDelegateExpression());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertFalse($timeout->getTimeDate() == null);
        $this->assertEquals("2019-09-09T12:12:12", $timeout->getTimeDate()->getRawTextContent());
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDuration());
    }

    public function testTimeoutDurationTaskListenerByDelegateExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
              ->userTask("task")
                ->taskListenerDelegateExpressionTimeoutWithDuration("timeout-1", "aDelegate", "PT1H")
            ->endEvent()
            ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $taskListeners = $extensionElements->getChildElementsByType(TaskListenerInterface::class);
        $this->assertCount(1, $taskListeners);

        $taskListener = $taskListeners[0];
        $this->assertEquals("aDelegate", $taskListener->getDelegateExpression());
        $this->assertEquals("timeout", $taskListener->getEvent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDate());
        $this->assertFalse($timeout->getTimeDuration() == null);
        $this->assertEquals("PT1H", $timeout->getTimeDuration()->getRawTextContent());
    }

    public function testExecutionListenerByClassName(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->executionListenerClass("start", "aClass")
          ->endEvent()
          ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $executionListeners = $extensionElements->getChildElementsByType(ExecutionListenerInterface::class);
        $this->assertCount(1, $executionListeners);

        $executionListener = $executionListeners[0];
        $this->assertEquals("aClass", $executionListener->getClass());
        $this->assertEquals("start", $executionListener->getEvent());
    }

    public function testExecutionListenerByExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->executionListenerExpression("start", "anExpression")
          ->endEvent()
          ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $extensionElements = $userTask->getExtensionElements();
        $executionListeners = $extensionElements->getChildElementsByType(ExecutionListenerInterface::class);
        $this->assertCount(1, $executionListeners);

        $executionListener = $executionListeners[0];
        $this->assertEquals("anExpression", $executionListener->getExpression());
        $this->assertEquals("start", $executionListener->getEvent());
    }

    public function testExecutionListenerByDelegateExpression(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->executionListenerDelegateExpression("start", "aDelegateExpression")
          ->endEvent()
          ->done();

          $userTask = $this->modelInstance->getModelElementById("task");
          $extensionElements = $userTask->getExtensionElements();
          $executionListeners = $extensionElements->getChildElementsByType(ExecutionListenerInterface::class);
          $this->assertCount(1, $executionListeners);

          $executionListener = $executionListeners[0];
          $this->assertEquals("aDelegateExpression", $executionListener->getDelegateExpression());
          $this->assertEquals("start", $executionListener->getEvent());
    }

    public function testMultiInstanceLoopCharacteristicsSequential(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->userTask("task")
          ->multiInstance()
            ->sequential()
            ->cardinality("card")
            ->completionCondition("compl")
            ->collection("coll")
            ->elementVariable("element")
          ->multiInstanceDone()
        ->endEvent()
        ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $miCharacteristics = $userTask->getChildElementsByType(MultiInstanceLoopCharacteristicsInterface::class);

        $this->assertCount(1, $miCharacteristics);

        $miCharacteristic = $miCharacteristics[0];
        $this->assertTrue($miCharacteristic->isSequential());
        $this->assertEquals("card", $miCharacteristic->getLoopCardinality()->getTextContent());
        $this->assertEquals("compl", $miCharacteristic->getCompletionCondition()->getTextContent());
        $this->assertEquals("coll", $miCharacteristic->getCollection());
        $this->assertEquals("element", $miCharacteristic->getElementVariable());
    }

    public function testMultiInstanceLoopCharacteristicsParallel(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
            ->multiInstance()
              ->parallel()
            ->multiInstanceDone()
          ->endEvent()
          ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $miCharacteristics = $userTask->getChildElementsByType(MultiInstanceLoopCharacteristicsInterface::class);

        $this->assertCount(1, $miCharacteristics);

        $miCharacteristic = $miCharacteristics[0];
        $this->assertFalse($miCharacteristic->isSequential());
    }

    public function testTaskWithInputOutput(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
            ->inputParameter("foo", "bar")
            ->inputParameter("yoo", "hoo")
            ->outputParameter("one", "two")
            ->outputParameter("three", "four")
          ->endEvent()
          ->done();

        $task = $this->modelInstance->getModelElementById("task");
        $this->assertInputOutputParameter($task);
    }

    protected function assertInputOutputParameter(BaseElementInterface $element): void
    {
        $inputOutput = $element->getExtensionElements()->getElementsQuery()
                       ->filterByType(InputOutputInterface::class)->singleResult();
        $this->assertFalse($inputOutput == null);

        $inputParameters = $inputOutput->getInputParameters();
        $this->assertCount(2, $inputParameters);

        $inputParameter = $inputParameters[0];
        $this->assertEquals("foo", $inputParameter->getName());
        $this->assertEquals("bar", $inputParameter->getTextContent());

        $inputParameter = $inputParameters[1];
        $this->assertEquals("yoo", $inputParameter->getName());
        $this->assertEquals("hoo", $inputParameter->getTextContent());

        $outputParameters = $inputOutput->getOutputParameters();
        $this->assertCount(2, $outputParameters);

        $outputParameter = $outputParameters[0];
        $this->assertEquals("one", $outputParameter->getName());
        $this->assertEquals("two", $outputParameter->getTextContent());

        $outputParameter = $outputParameters[1];
        $this->assertEquals("three", $outputParameter->getName());
        $this->assertEquals("four", $outputParameter->getTextContent());
    }

    public function testMultiInstanceLoopCharacteristicsAsynchronousMultiInstanceAsyncBeforeElement(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
                ->startEvent()
                ->userTask("task")
                ->multiInstance()
                ->asyncBefore()
                ->parallel()
                ->multiInstanceDone()
                ->endEvent()
                ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $miCharacteristics = $userTask->getChildElementsByType(MultiInstanceLoopCharacteristicsInterface::class);

        $this->assertCount(1, $miCharacteristics);

        $miCharacteristic = $miCharacteristics[0];
        $this->assertFalse($miCharacteristic->isSequential());
        $this->assertFalse($miCharacteristic->isAsyncAfter());
        $this->assertTrue($miCharacteristic->isAsyncBefore());
    }

    public function testMultiInstanceLoopCharacteristicsAsynchronousMultiInstanceAsyncAfterElement(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent()
        ->userTask("task")
        ->multiInstance()
        ->asyncAfter()
        ->parallel()
        ->multiInstanceDone()
        ->endEvent()
        ->done();

        $userTask = $this->modelInstance->getModelElementById("task");
        $miCharacteristics = $userTask->getChildElementsByType(MultiInstanceLoopCharacteristicsInterface::class);

        $this->assertCount(1, $miCharacteristics);

        $miCharacteristic = $miCharacteristics[0];
        $this->assertFalse($miCharacteristic->isSequential());
        $this->assertFalse($miCharacteristic->isAsyncBefore());
        $this->assertTrue($miCharacteristic->isAsyncAfter());
    }

    public function testTaskWithInputOutputWithExistingExtensionElements(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
            ->executionListenerExpression("end", '${true}')
            ->inputParameter("foo", "bar")
            ->inputParameter("yoo", "hoo")
            ->outputParameter("one", "two")
            ->outputParameter("three", "four")
          ->endEvent()
          ->done();

        $task = $this->modelInstance->getModelElementById("task");
        $this->assertInputOutputParameter($task);
    }

    public function testTaskWithInputOutputWithExistingInputOutput(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
            ->inputParameter("foo", "bar")
            ->outputParameter("one", "two")
          ->endEvent()
          ->done();

        $task = $this->modelInstance->getModelElementById("task");

        $task->builder()
          ->inputParameter("yoo", "hoo")
          ->outputParameter("three", "four");

        $this->assertInputOutputParameter($task);
    }

    public function testSubProcessWithInputOutputWithExistingExtensionElements(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->subProcess("subProcess")
            ->executionListenerExpression("end", '${true}')
            ->inputParameter("foo", "bar")
            ->inputParameter("yoo", "hoo")
            ->outputParameter("one", "two")
            ->outputParameter("three", "four")
            ->embeddedSubProcess()
              ->startEvent()
              ->endEvent()
            ->subProcessDone()
          ->endEvent()
          ->done();

        $subProcess = $this->modelInstance->getModelElementById("subProcess");
        $this->assertInputOutputParameter($subProcess);
    }

    public function testSubProcessWithInputOutputWithExistingInputOutput(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->subProcess("subProcess")
            ->inputParameter("foo", "bar")
            ->outputParameter("one", "two")
            ->embeddedSubProcess()
              ->startEvent()
              ->endEvent()
            ->subProcessDone()
          ->endEvent()
          ->done();

        $subProcess = $this->modelInstance->getModelElementById("subProcess");

        $subProcess->builder()
          ->inputParameter("yoo", "hoo")
          ->outputParameter("three", "four");

        $this->assertInputOutputParameter($subProcess);
    }

    public function testTimerStartEventWithDate(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent("start")->timerWithDate(self::TIMER_DATE)
        ->done();

        $this->assertTimerWithDate("start", self::TIMER_DATE);
    }

    public function testTimerStartEventWithDuration(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent("start")->timerWithDuration(self::TIMER_DURATION)
        ->done();

        $this->assertTimerWithDuration("start", self::TIMER_DURATION);
    }

    public function testTimerStartEventWithCycle(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
        ->startEvent("start")->timerWithCycle(self::TIMER_CYCLE)
        ->done();

        $this->assertTimerWithCycle("start", self::TIMER_CYCLE);
    }

    public function testIntermediateTimerCatchEventWithDate(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent("catch")->timerWithDate(self::TIMER_DATE)
          ->done();

        $this->assertTimerWithDate("catch", self::TIMER_DATE);
    }

    public function testIntermediateTimerCatchEventWithDuration(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent("catch")->timerWithDuration(self::TIMER_DURATION)
          ->done();

        $this->assertTimerWithDuration("catch", self::TIMER_DURATION);
    }

    public function testIntermediateTimerCatchEventWithCycle(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent("catch")->timerWithCycle(self::TIMER_CYCLE)
          ->done();

        $this->assertTimerWithCycle("catch", self::TIMER_CYCLE);
    }

    protected function assertTimerWithDate(string $elementId, string $timerDate): void
    {
        $timerEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            TimerEventDefinitionInterface::class
        );
        $timeDate = $timerEventDefinition->getTimeDate();
        $this->assertFalse($timeDate == null);
        $this->assertEquals($timerDate, $timeDate->getTextContent());
    }

    protected function assertTimerWithDuration(string $elementId, string $timerDuration): void
    {
        $timerEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            TimerEventDefinitionInterface::class
        );
        $timeDuration = $timerEventDefinition->getTimeDuration();
        $this->assertFalse($timeDuration == null);
        $this->assertEquals($timerDuration, $timeDuration->getTextContent());
    }

    protected function assertTimerWithCycle(string $elementId, string $timerCycle): void
    {
        $timerEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            TimerEventDefinitionInterface::class
        );
        $timeCycle = $timerEventDefinition->getTimeCycle();
        $this->assertFalse($timeCycle == null);
        $this->assertEquals($timerCycle, $timeCycle->getTextContent());
    }

    public function testTimerBoundaryEventWithDate(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->timerWithDate(self::TIMER_DATE)
          ->done();

        $this->assertTimerWithDate("boundary", self::TIMER_DATE);
    }

    public function testTimerBoundaryEventWithDuration(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->timerWithDuration(self::TIMER_DURATION)
          ->done();

        $this->assertTimerWithDuration("boundary", self::TIMER_DURATION);
    }

    public function testTimerBoundaryEventWithCycle(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->timerWithCycle(self::TIMER_CYCLE)
          ->done();

        $this->assertTimerWithCycle("boundary", self::TIMER_CYCLE);
    }

    public function testNotCancelingBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask()
          ->boundaryEvent("boundary")->cancelActivity(false)
          ->done();

        $boundaryEvent = $this->modelInstance->getModelElementById("boundary");
        $this->assertFalse($boundaryEvent->cancelActivity());
    }

    public function testCatchAllErrorBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->error()
          ->endEvent("boundaryEnd")
          ->done();

        $errorEventDefinition = $this->assertAndGetSingleEventDefinition(
            "boundary",
            ErrorEventDefinitionInterface::class
        );
        $this->assertNull($errorEventDefinition->getError());
    }

    public function testCompensationTask(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->boundaryEvent("boundary")
            ->compensateEventDefinition()->compensateEventDefinitionDone()
            ->compensationStart()
            ->userTask("compensate")->name("compensate")
            ->compensationDone()
          ->endEvent("theend")
          ->done();

        // Checking Association
        $associations = $this->modelInstance->getModelElementsByType(AssociationInterface::class);
        $this->assertCount(1, $associations);
        $association = $associations[0];
        $this->assertEquals("boundary", $association->getSource()->getId());
        $this->assertEquals("compensate", $association->getTarget()->getId());
        $this->assertEquals("One", $association->getAssociationDirection());

        // Checking Sequence flow
        $task = $this->modelInstance->getModelElementById("task");
        $outgoing = $task->getOutgoing();
        $this->assertCount(1, $outgoing);
        $flow = $outgoing[0];
        $this->assertEquals("task", $flow->getSource()->getId());
        $this->assertEquals("theend", $flow->getTarget()->getId());
    }

    public function testOnlyOneCompensateBoundaryEventAllowed(): void
    {
        // given
        $builder = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->boundaryEvent("boundary")
          ->compensateEventDefinition()->compensateEventDefinitionDone()
          ->compensationStart()
          ->userTask("compensate")->name("compensate");

        // then
        $this->expectException(BpmnModelException::class);

        // when
        $builder->userTask();
    }

    public function testInvalidCompensationStartCall(): void
    {
        // given
        $builder = Bpmn::getInstance()->createProcess()->startEvent();

        // then
        $this->expectException(BpmnModelException::class);

        // when
        $builder->compensationStart();
    }

    public function testInvalidCompensationDoneCall(): void
    {
        // given
        $builder = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->boundaryEvent("boundary")
          ->compensateEventDefinition()->compensateEventDefinitionDone();

        // then
        $this->expectException(BpmnModelException::class);

        // when
        $builder->compensationDone();
    }

    public function testErrorBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->error("myErrorCode", "errorMessage")
          ->endEvent("boundaryEnd")
          ->done();

        $this->assertErrorEventDefinition("boundary", "myErrorCode", "errorMessage");

        $userTask = $this->modelInstance->getModelElementById("task");
        $boundaryEvent = $this->modelInstance->getModelElementById("boundary");
        $boundaryEnd = $this->modelInstance->getModelElementById("boundaryEnd");

        // boundary event is attached to the user task
        $this->assertTrue($boundaryEvent->getAttachedTo()->equals($userTask));

        // boundary event has no incoming sequence flows
        $this->assertEmpty($boundaryEvent->getIncoming());

        // the next flow node is the boundary end event
        $succeedingNodes = $boundaryEvent->getSucceedingNodes()->list();
        foreach ($succeedingNodes as $node) {
            $this->assertTrue($node->equals($boundaryEnd));
        }
    }

    public function testErrorBoundaryEventWithoutErrorMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
            ->userTask("task")
            ->endEvent()
            ->moveToActivity("task")
            ->boundaryEvent("boundary")->error("myErrorCode")
            ->endEvent("boundaryEnd")
            ->done();

        $this->assertErrorEventDefinition("boundary", "myErrorCode", null);
    }

    public function testErrorDefinitionForBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")
            ->errorEventDefinition("event")
              ->errorCodeVariable("errorCodeVariable")
              ->errorMessageVariable("errorMessageVariable")
              ->error("errorCode", "errorMessage")
            ->errorEventDefinitionDone()
          ->endEvent("boundaryEnd")
          ->done();

        $this->assertErrorEventDefinition("boundary", "errorCode", "errorMessage");
        $this->assertErrorEventDefinitionForErrorVariables("boundary", "errorCodeVariable", "errorMessageVariable");
    }

    public function testErrorDefinitionForBoundaryEventWithoutEventDefinitionId(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")
            ->errorEventDefinition()
              ->errorCodeVariable("errorCodeVariable")
              ->errorMessageVariable("errorMessageVariable")
              ->error("errorCode", "errorMessage")
            ->errorEventDefinitionDone()
          ->endEvent("boundaryEnd")
          ->done();

        $path = tempnam(sys_get_temp_dir(), 'bpmn');
        $fd = fopen($path, 'a+');
        Bpmn::getInstance()->writeModelToStream($fd, $this->modelInstance);

        $this->assertErrorEventDefinition("boundary", "errorCode", "errorMessage");
        $this->assertErrorEventDefinitionForErrorVariables("boundary", "errorCodeVariable", "errorMessageVariable");

        unlink($path);
    }

    public function testErrorEndEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end")->error("myErrorCode", "errorMessage")
          ->done();

        $this->assertErrorEventDefinition("end", "myErrorCode", "errorMessage");
    }

    public function testErrorEndEventWithoutErrorMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
            ->startEvent()
            ->endEvent("end")->error("myErrorCode")
            ->done();

        $this->assertErrorEventDefinition("end", "myErrorCode", null);
    }

    public function testErrorEndEventWithExistingError(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent("end")->error("myErrorCode", "errorMessage")
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->error("myErrorCode")
          ->endEvent("boundaryEnd")
          ->done();

        $boundaryError = $this->assertErrorEventDefinition("boundary", "myErrorCode", "errorMessage");
        $endError = $this->assertErrorEventDefinition("end", "myErrorCode", "errorMessage");

        $this->assertTrue($boundaryError->equals($endError));

        $this->assertOnlyOneErrorExists("myErrorCode");
    }

    protected function assertOnlyOneErrorExists(string $errorCode): void
    {
        $errors = $this->modelInstance->getModelElementsByType(ErrorInterface::class);
        foreach ($errors as $error) {
            $this->assertEquals($errorCode, $error->getErrorCode());
        }
    }

    public function testErrorStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subProcessStart")
            ->error("myErrorCode", "errorMessage")
            ->endEvent()
          ->done();

        $this->assertErrorEventDefinition("subProcessStart", "myErrorCode", "errorMessage");
    }

    public function testErrorStartEventWithoutErrorMessage(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
              ->startEvent("subProcessStart")
              ->error("myErrorCode")
              ->endEvent()
          ->done();

        $this->assertErrorEventDefinition("subProcessStart", "myErrorCode", null);
    }

    public function testCatchAllErrorStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subProcessStart")
            ->error()
            ->endEvent()
          ->done();

        $errorEventDefinition = $this->assertAndGetSingleEventDefinition(
            "subProcessStart",
            ErrorEventDefinitionInterface::class
        );
        $this->assertNull($errorEventDefinition->getError());
    }

    public function testCatchAllEscalationBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent()
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->escalation()
          ->endEvent("boundaryEnd")
          ->done();

        $escalationEventDefinition = $this->assertAndGetSingleEventDefinition(
            "boundary",
            EscalationEventDefinitionInterface::class
        );
        $this->assertNull($escalationEventDefinition->getEscalation());
    }

    public function testEscalationBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->subProcess("subProcess")
          ->endEvent()
          ->moveToActivity("subProcess")
          ->boundaryEvent("boundary")->escalation("myEscalationCode")
          ->endEvent("boundaryEnd")
          ->done();

        $this->assertEscalationEventDefinition("boundary", "myEscalationCode");

        $subProcess = $this->modelInstance->getModelElementById("subProcess");
        $boundaryEvent = $this->modelInstance->getModelElementById("boundary");
        $boundaryEnd = $this->modelInstance->getModelElementById("boundaryEnd");

        // boundary event is attached to the sub process
        $this->assertTrue($boundaryEvent->getAttachedTo()->equals($subProcess));

        // boundary event has no incoming sequence flows
        $this->assertEmpty($boundaryEvent->getIncoming());

        // the next flow node is the boundary end event
        $succeedingNodes = $boundaryEvent->getSucceedingNodes()->list();
        foreach ($succeedingNodes as $node) {
            $this->assertTrue($node->equals($boundaryEnd));
        }
    }

    protected function assertEscalationEventDefinition(string $elementId, string $escalationCode): EscalationInterface
    {
        $escalationEventDefinition = $this->assertAndGetSingleEventDefinition(
            $elementId,
            EscalationEventDefinitionInterface::class
        );
        $escalation = $escalationEventDefinition->getEscalation();
        $this->assertFalse($escalation == null);
        $this->assertEquals($escalationCode, $escalation->getEscalationCode());

        return $escalation;
    }

    public function testEscalationEndEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent("end")->escalation("myEscalationCode")
          ->done();

        $this->assertEscalationEventDefinition("end", "myEscalationCode");
    }

    public function testEscalationStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subProcessStart")
            ->escalation("myEscalationCode")
            ->endEvent()
          ->done();

        $this->assertEscalationEventDefinition("subProcessStart", "myEscalationCode");
    }

    public function testCatchAllEscalationStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subProcessStart")
            ->escalation()
            ->endEvent()
          ->done();

        $escalationEventDefinition = $this->assertAndGetSingleEventDefinition(
            "subProcessStart",
            EscalationEventDefinitionInterface::class
        );
        $this->assertNull($escalationEventDefinition->getEscalation());
    }

    public function testIntermediateEscalationThrowEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateThrowEvent("throw")->escalation("myEscalationCode")
          ->endEvent()
          ->done();

        $this->assertEscalationEventDefinition("throw", "myEscalationCode");
    }

    public function testEscalationEndEventWithExistingEscalation(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("task")
          ->endEvent("end")->escalation("myEscalationCode")
          ->moveToActivity("task")
          ->boundaryEvent("boundary")->escalation("myEscalationCode")
          ->endEvent("boundaryEnd")
          ->done();

        $boundaryEscalation = $this->assertEscalationEventDefinition("boundary", "myEscalationCode");
        $endEscalation = $this->assertEscalationEventDefinition("end", "myEscalationCode");

        $this->assertTrue($boundaryEscalation->equals($endEscalation));

        $this->assertOnlyOneEscalationExists("myEscalationCode");
    }

    protected function assertOnlyOneEscalationExists(string $escalationCode): void
    {
        $escalations = $this->modelInstance->getModelElementsByType(EscalationInterface::class);
        foreach ($escalations as $escalation) {
            $this->assertEquals($escalationCode, $escalation->getEscalationCode());
        }
    }

    public function testCompensationStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subProcessStart")
            ->compensation()
            ->endEvent()
          ->done();

        $this->assertCompensationEventDefinition("subProcessStart");
    }

    protected function assertCompensationEventDefinition(string $elementId): void
    {
        $this->assertAndGetSingleEventDefinition($elementId, CompensateEventDefinitionInterface::class);
    }

    public function testInterruptingStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subProcessStart")
              ->interrupting(true)
              ->error()
            ->endEvent()
          ->done();

        $startEvent = $this->modelInstance->getModelElementById("subProcessStart");
        $this->assertFalse($startEvent == null);
        $this->assertTrue($startEvent->isInterrupting());
    }

    public function testNonInterruptingStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent("subProcessStart")
              ->interrupting(false)
              ->error()
            ->endEvent()
          ->done();

        $startEvent = $this->modelInstance->getModelElementById("subProcessStart");
        $this->assertFalse($startEvent == null);
        $this->assertFalse($startEvent->isInterrupting());
    }

    public function testUserTaskFormField(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask(BpmnTestConstants::TASK_ID)
            ->formField()
              ->id("myFormField_1")
              ->label("Form Field One")
              ->type("string")
              ->defaultValue("myDefaultVal_1")
            ->formFieldDone()
            ->formField()
              ->id("myFormField_2")
              ->label("Form Field Two")
              ->type("integer")
              ->defaultValue("myDefaultVal_2")
            ->formFieldDone()
          ->endEvent()
          ->done();

        $userTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);
        $this->assertFormField($userTask);
    }

    protected function assertFormField(BaseElementInterface $element): void
    {
        $this->assertFalse($element->getExtensionElements() == null);

        $formData = $element->getExtensionElements()->getElementsQuery()
                    ->filterByType(FormDataInterface::class)->singleResult();
        $this->assertFalse($formData == null);

        $formFields = $formData->getFormFields();
        $this->assertCount(2, $formFields);

        $formField = $formFields[0];
        $this->assertEquals("myFormField_1", $formField->getId());
        $this->assertEquals("Form Field One", $formField->getLabel());
        $this->assertEquals("string", $formField->getType());
        $this->assertEquals("myDefaultVal_1", $formField->getDefaultValue());

        $formField = $formFields[1];
        $this->assertEquals("myFormField_2", $formField->getId());
        $this->assertEquals("Form Field Two", $formField->getLabel());
        $this->assertEquals("integer", $formField->getType());
        $this->assertEquals("myDefaultVal_2", $formField->getDefaultValue());
    }

    public function testUserTaskFormFieldWithExistingFormData(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask(BpmnTestConstants::TASK_ID)
            ->formField()
              ->id("myFormField_1")
              ->label("Form Field One")
              ->type("string")
              ->defaultValue("myDefaultVal_1")
            ->formFieldDone()
          ->endEvent()
          ->done();

        $userTask = $this->modelInstance->getModelElementById(BpmnTestConstants::TASK_ID);

        $userTask->builder()
          ->formField()
            ->id("myFormField_2")
            ->label("Form Field Two")
            ->type("integer")
            ->defaultValue("myDefaultVal_2")
          ->formFieldDone();

        $this->assertFormField($userTask);
    }

    public function testStartEventFormField(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->formField()
              ->id("myFormField_1")
              ->label("Form Field One")
              ->type("string")
              ->defaultValue("myDefaultVal_1")
            ->formFieldDone()
            ->formField()
            ->id("myFormField_2")
              ->label("Form Field Two")
              ->type("integer")
              ->defaultValue("myDefaultVal_2")
            ->formFieldDone()
          ->endEvent()
          ->done();

        $startEvent = $this->modelInstance->getModelElementById(BpmnTestConstants::START_EVENT_ID);
        $this->assertFormField($startEvent);
    }

    public function testCompensateEventDefintionCatchStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent("start")
            ->compensateEventDefinition()
            ->waitForCompletion(false)
            ->compensateEventDefinitionDone()
          ->userTask("userTask")
          ->endEvent("end")
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition("start", CompensateEventDefinitionInterface::class);
        $activity = $eventDefinition->getActivity();
        $this->assertNull($activity);
        $this->assertFalse($eventDefinition->isWaitForCompletion());
    }

    public function testCompensateEventDefintionCatchBoundaryEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("userTask")
          ->boundaryEvent("catch")
            ->compensateEventDefinition()
            ->waitForCompletion(false)
            ->compensateEventDefinitionDone()
          ->endEvent("end")
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition("catch", CompensateEventDefinitionInterface::class);
        $activity = $eventDefinition->getActivity();
        $this->assertNull($activity);
        $this->assertFalse($eventDefinition->isWaitForCompletion());
    }

    public function testCompensateEventDefintionCatchBoundaryEventWithId(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("userTask")
          ->boundaryEvent("catch")
            ->compensateEventDefinition("foo")
            ->waitForCompletion(false)
            ->compensateEventDefinitionDone()
          ->endEvent("end")
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition("catch", CompensateEventDefinitionInterface::class);
        $this->assertEquals("foo", $eventDefinition->getId());
    }

    public function testCompensateEventDefintionThrowEndEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("userTask")
          ->endEvent("end")
            ->compensateEventDefinition()
            ->activityRef("userTask")
            ->waitForCompletion(true)
            ->compensateEventDefinitionDone()
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition("end", CompensateEventDefinitionInterface::class);
        $activity = $eventDefinition->getActivity();
        $this->assertTrue($activity->equals($this->modelInstance->getModelElementById("userTask")));
        $this->assertTrue($eventDefinition->isWaitForCompletion());
    }

    public function testCompensateEventDefintionThrowIntermediateEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("userTask")
          ->intermediateThrowEvent("throw")
            ->compensateEventDefinition()
            ->activityRef("userTask")
            ->waitForCompletion(true)
            ->compensateEventDefinitionDone()
          ->endEvent("end")
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition("throw", CompensateEventDefinitionInterface::class);
        $activity = $eventDefinition->getActivity();
        $this->assertTrue($activity->equals($this->modelInstance->getModelElementById("userTask")));
        $this->assertTrue($eventDefinition->isWaitForCompletion());
    }

    public function testCompensateEventDefintionThrowIntermediateEventWithId(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("userTask")
          ->intermediateCatchEvent("throw")
            ->compensateEventDefinition("foo")
            ->activityRef("userTask")
            ->waitForCompletion(true)
            ->compensateEventDefinitionDone()
          ->endEvent("end")
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition("throw", CompensateEventDefinitionInterface::class);
        $this->assertEquals("foo", $eventDefinition->getId());
    }

    public function testCompensateEventDefintionReferencesNonExistingActivity(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("userTask")
          ->endEvent("end")
          ->done();

        $userTask = $this->modelInstance->getModelElementById("userTask");
        $userTaskBuilder = $userTask->builder();

        $this->expectException(BpmnModelException::class);
        $userTaskBuilder
            ->boundaryEvent()
            ->compensateEventDefinition()
            ->activityRef("nonExistingTask")
            ->done();
    }

    public function testCompensateEventDefintionReferencesActivityInDifferentScope(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask("userTask")
          ->subProcess()
            ->embeddedSubProcess()
            ->startEvent()
            ->userTask("subProcessTask")
            ->endEvent()
            ->subProcessDone()
          ->endEvent("end")
          ->done();

        $userTask = $this->modelInstance->getModelElementById("userTask");
        $userTaskBuilder = $userTask->builder();

        $this->expectException(BpmnModelException::class);
        $userTaskBuilder
            ->boundaryEvent()
            ->compensateEventDefinition()
            ->activityRef("subProcessTask")
            ->done();
    }

    public function testConditionalEventDefinitionCamundaExtensions(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent()
          ->conditionalEventDefinition(BpmnTestConstants::CONDITION_ID)
            ->condition(BpmnTestConstants::TEST_CONDITION)
            ->variableEvents(BpmnTestConstants::TEST_CONDITIONAL_VARIABLE_EVENTS)
            ->variableEvents(BpmnTestConstants::TEST_CONDITIONAL_VARIABLE_EVENTS_LIST)
            ->variableName(BpmnTestConstants::TEST_CONDITIONAL_VARIABLE_NAME)
          ->conditionalEventDefinitionDone()
          ->endEvent()
          ->done();

        $conditionalEventDef = $this->modelInstance->getModelElementById(BpmnTestConstants::CONDITION_ID);
        $this->assertEquals(
            BpmnTestConstants::TEST_CONDITIONAL_VARIABLE_EVENTS,
            $conditionalEventDef->getVariableEvents()
        );
        $this->assertTrue(
            $conditionalEventDef->getVariableEventsList() == BpmnTestConstants::TEST_CONDITIONAL_VARIABLE_EVENTS_LIST
        );
        $this->assertEquals(BpmnTestConstants::TEST_CONDITIONAL_VARIABLE_NAME, $conditionalEventDef->getVariableName());
    }

    public function testIntermediateConditionalEventDefinition(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->intermediateCatchEvent(BpmnTestConstants::CATCH_ID)
            ->conditionalEventDefinition(BpmnTestConstants::CONDITION_ID)
                ->condition(BpmnTestConstants::TEST_CONDITION)
            ->conditionalEventDefinitionDone()
          ->endEvent()
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition(
            BpmnTestConstants::CATCH_ID,
            ConditionalEventDefinitionInterface::class
        );
        $this->assertEquals(BpmnTestConstants::CONDITION_ID, $eventDefinition->getId());
        $this->assertEquals(BpmnTestConstants::TEST_CONDITION, $eventDefinition->getCondition()->getTextContent());
    }

    public function testIntermediateConditionalEventDefinitionShortCut(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
            ->intermediateCatchEvent(BpmnTestConstants::CATCH_ID)
            ->condition(null, BpmnTestConstants::TEST_CONDITION)
          ->endEvent()
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition(
            BpmnTestConstants::CATCH_ID,
            ConditionalEventDefinitionInterface::class
        );
        $this->assertEquals(BpmnTestConstants::TEST_CONDITION, $eventDefinition->getCondition()->getTextContent());
    }

    public function testBoundaryConditionalEventDefinition(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask(BpmnTestConstants::USER_TASK_ID)
          ->endEvent()
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
              ->boundaryEvent(BpmnTestConstants::BOUNDARY_ID)
                ->conditionalEventDefinition(BpmnTestConstants::CONDITION_ID)
                  ->condition(BpmnTestConstants::TEST_CONDITION)
                ->conditionalEventDefinitionDone()
              ->endEvent()
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition(
            BpmnTestConstants::BOUNDARY_ID,
            ConditionalEventDefinitionInterface::class
        );
        $this->assertEquals(BpmnTestConstants::CONDITION_ID, $eventDefinition->getId());
        $this->assertEquals(BpmnTestConstants::TEST_CONDITION, $eventDefinition->getCondition()->getTextContent());
    }

    public function testEventSubProcessConditionalStartEvent(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createProcess()
          ->startEvent()
          ->userTask()
          ->endEvent()
          ->subProcess()
            ->triggerByEvent()
            ->embeddedSubProcess()
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
              ->conditionalEventDefinition(BpmnTestConstants::CONDITION_ID)
                ->condition(BpmnTestConstants::TEST_CONDITION)
              ->conditionalEventDefinitionDone()
            ->endEvent()
          ->done();

        $eventDefinition = $this->assertAndGetSingleEventDefinition(
            BpmnTestConstants::START_EVENT_ID,
            ConditionalEventDefinitionInterface::class
        );
        $this->assertEquals(BpmnTestConstants::CONDITION_ID, $eventDefinition->getId());
        $this->assertEquals(BpmnTestConstants::TEST_CONDITION, $eventDefinition->getCondition()->getTextContent());
    }
}
