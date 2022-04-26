<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    BpmnModelElementInstanceInterface,
    BusinessRuleTaskInterface,
    CallActivityInterface,
    EndEventInterface,
    ErrorInterface,
    ErrorEventDefinitionInterface,
    ExpressionInterface,
    MessageEventDefinitionInterface,
    ParallelGatewayInterface,
    ProcessInterface,
    ScriptTaskInterface,
    SendTaskInterface,
    SequenceFlowInterface,
    ServiceTaskInterface,
    StartEventInterface,
    TimerEventDefinitionInterface,
    UserTaskInterface
};
use Jabe\Model\Bpmn\Instance\Extension\{
    ConnectorInterface,
    ConnectorIdInterface,
    ConstraintInterface,
    EntryInterface,
    ExecutionListenerInterface,
    FailedJobRetryTimeCycleInterface,
    FieldInterface,
    FormDataInterface,
    FormFieldInterface,
    FormPropertyInterface,
    InInterface,
    InputOutputInterface,
    InputParameterInterface,
    ListInterface,
    MapInterface,
    OutInterface,
    OutputParameterInterface,
    PotentialStarterInterface,
    PropertiesInterface,
    PropertyInterface,
    ScriptInterface,
    TaskListenerInterface,
    ValueInterface
};

class ExtensionsTest extends TestCase
{
    protected $process;
    protected $startEvent;
    protected $sequenceFlow;
    protected $userTask;
    protected $serviceTask;
    protected $sendTask;
    protected $scriptTask;
    protected $callActivity;
    protected $businessRuleTask;
    protected $endEvent;
    protected $messageEventDefinition;
    protected $parallelGateway;
    protected $namespace;
    protected $modelInstance;
    protected $error;

    protected function setUp(): void
    {
        $stream = fopen('tests/Bpmn/Resources/ExtensionsTest.xml', 'r+');
        $this->modelInstance = Bpmn::getInstance()->readModelFromStream($stream);
        $this->namespace = BpmnModelConstants::EXTENSION_NS;

        $this->prepareModel();
    }

    public function prepareModel(): void
    {
        $this->process = $this->modelInstance->getModelElementById(BpmnTestConstants::PROCESS_ID);
        $this->startEvent = $this->modelInstance->getModelElementById(BpmnTestConstants::START_EVENT_ID);
        $this->sequenceFlow = $this->modelInstance->getModelElementById(BpmnTestConstants::SEQUENCE_FLOW_ID);
        $this->userTask = $this->modelInstance->getModelElementById(BpmnTestConstants::USER_TASK_ID);
        $this->serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID);
        $this->sendTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SEND_TASK_ID);
        $this->scriptTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SCRIPT_TASK_ID);
        $this->callActivity = $this->modelInstance->getModelElementById(BpmnTestConstants::CALL_ACTIVITY_ID);
        $this->businessRuleTask = $this->modelInstance->getModelElementById(BpmnTestConstants::BUSINESS_RULE_TASK);
        $this->endEvent = $this->modelInstance->getModelElementById(BpmnTestConstants::END_EVENT_ID);
        $this->messageEventDefinition = $this->endEvent->getEventDefinitions()[0];
        $this->parallelGateway = $this->modelInstance->getModelElementById("parallelGateway");
        $this->error = $this->modelInstance->getModelElementById("error");
    }

    public function testAssignee(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->userTask->getAssignee());
        $this->userTask->setAssignee(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->userTask->getAssignee());
    }

    public function testAsync(): void
    {
        $this->assertFalse($this->startEvent->isAsync());
        $this->assertTrue($this->userTask->isAsync());
        $this->assertTrue($this->parallelGateway->isAsync());

        $this->startEvent->setAsync(true);
        $this->userTask->setAsync(false);
        $this->parallelGateway->setAsync(false);

        $this->assertTrue($this->startEvent->isAsync());
        $this->assertFalse($this->userTask->isAsync());
        $this->assertFalse($this->parallelGateway->isAsync());
    }

    public function testAsyncBefore(): void
    {
        $this->assertTrue($this->startEvent->isAsyncBefore());
        $this->assertTrue($this->endEvent->isAsyncBefore());
        $this->assertTrue($this->userTask->isAsyncBefore());
        $this->assertTrue($this->parallelGateway->isAsyncBefore());

        $this->startEvent->setAsyncBefore(false);
        $this->endEvent->setAsyncBefore(false);
        $this->userTask->setAsyncBefore(false);
        $this->parallelGateway->setAsyncBefore(false);

        $this->assertFalse($this->startEvent->isAsyncBefore());
        $this->assertFalse($this->endEvent->isAsyncBefore());
        $this->assertFalse($this->userTask->isAsyncBefore());
        $this->assertFalse($this->parallelGateway->isAsyncBefore());
    }

    public function testAsyncAfter(): void
    {
        $this->assertTrue($this->startEvent->isAsyncAfter());
        $this->assertTrue($this->endEvent->isAsyncAfter());
        $this->assertTrue($this->userTask->isAsyncAfter());
        $this->assertTrue($this->parallelGateway->isAsyncAfter());

        $this->startEvent->setAsyncAfter(false);
        $this->endEvent->setAsyncAfter(false);
        $this->userTask->setAsyncAfter(false);
        $this->parallelGateway->setAsyncAfter(false);

        $this->assertFalse($this->startEvent->isAsyncAfter());
        $this->assertFalse($this->endEvent->isAsyncAfter());
        $this->assertFalse($this->userTask->isAsyncAfter());
        $this->assertFalse($this->parallelGateway->isAsyncAfter());
    }

    public function testFlowNodeJobPriority(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_FLOW_NODE_JOB_PRIORITY, $this->startEvent->getJobPriority());
        $this->assertEquals(BpmnTestConstants::TEST_FLOW_NODE_JOB_PRIORITY, $this->endEvent->getJobPriority());
        $this->assertEquals(BpmnTestConstants::TEST_FLOW_NODE_JOB_PRIORITY, $this->userTask->getJobPriority());
        $this->assertEquals(BpmnTestConstants::TEST_FLOW_NODE_JOB_PRIORITY, $this->parallelGateway->getJobPriority());
    }

    public function testProcessJobPriority(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_PROCESS_JOB_PRIORITY, $this->process->getJobPriority());
    }

    public function testProcessTaskPriority(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_PROCESS_TASK_PRIORITY, $this->process->getTaskPriority());
    }

    public function testHistoryTimeToLive(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_HISTORY_TIME_TO_LIVE, $this->process->getHistoryTimeToLive());
    }

    public function testIsStartableInTasklist(): void
    {
        $this->assertFalse($this->process->isStartableInTasklist());
    }

    public function testVersionTag(): void
    {
        $this->assertEquals("v1.0.0", $this->process->getVersionTag());
    }

    public function testServiceTaskPriority(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $this->serviceTask->getTaskPriority());
    }

    public function testCalledElementBinding(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCalledElementBinding());
        $this->callActivity->setCalledElementBinding(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCalledElementBinding());
    }

    public function testCalledElementVersion(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCalledElementVersion());
        $this->callActivity->setCalledElementVersion(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCalledElementVersion());
    }

    public function testCalledElementVersionTag(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCalledElementVersionTag());
        $this->callActivity->setCalledElementVersionTag(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCalledElementVersionTag());
    }

    public function testCalledElementTenantId(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCalledElementTenantId());
        $this->callActivity->setCalledElementTenantId(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCalledElementTenantId());
    }

    public function testCaseRef(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCaseRef());
        $this->callActivity->setCaseRef(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCaseRef());
    }

    public function testCaseBinding(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCaseBinding());
        $this->callActivity->setCaseBinding(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCaseBinding());
    }

    public function testCaseVersion(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCaseVersion());
        $this->callActivity->setCaseVersion(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCaseVersion());
    }

    public function testCaseTenantId(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->callActivity->getCaseTenantId());
        $this->callActivity->setCaseTenantId(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->callActivity->getCaseTenantId());
    }

    public function testDecisionRef(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->businessRuleTask->getDecisionRef());
        $this->businessRuleTask->setDecisionRef(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->businessRuleTask->getDecisionRef());
    }

    public function testDecisionRefBinding(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->businessRuleTask->getDecisionRefBinding());
        $this->businessRuleTask->setDecisionRefBinding(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->businessRuleTask->getDecisionRefBinding());
    }

    public function testDecisionRefVersion(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->businessRuleTask->getDecisionRefVersion());
        $this->businessRuleTask->setDecisionRefVersion(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->businessRuleTask->getDecisionRefVersion());
    }

    public function testDecisionRefVersionTag(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->businessRuleTask->getDecisionRefVersionTag());
        $this->businessRuleTask->setDecisionRefVersionTag(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->businessRuleTask->getDecisionRefVersionTag());
    }

    public function testDecisionRefTenantId(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->businessRuleTask->getDecisionRefTenantId());
        $this->businessRuleTask->setDecisionRefTenantId(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->businessRuleTask->getDecisionRefTenantId());
    }

    public function testMapDecisionResult(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->businessRuleTask->getMapDecisionResult());
        $this->businessRuleTask->setMapDecisionResult(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->businessRuleTask->getMapDecisionResult());
    }

    public function testTaskPriority(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->businessRuleTask->getTaskPriority());
        $this->businessRuleTask->setTaskPriority(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY);
        $this->assertEquals(BpmnTestConstants::TEST_SERVICE_TASK_PRIORITY, $this->businessRuleTask->getTaskPriority());
    }

    public function testCandidateGroups(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_XML, $this->userTask->getCandidateGroups());
        $this->assertTrue($this->userTask->getCandidateGroupsList() == BpmnTestConstants::TEST_GROUPS_LIST_XML);
        $this->userTask->setCandidateGroups(BpmnTestConstants::TEST_GROUPS_API);
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_API, $this->userTask->getCandidateGroups());
        $this->assertTrue($this->userTask->getCandidateGroupsList() == BpmnTestConstants::TEST_GROUPS_LIST_API);
        $this->userTask->setCandidateGroupsList(BpmnTestConstants::TEST_GROUPS_LIST_XML);
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_XML, $this->userTask->getCandidateGroups());
        $this->assertTrue($this->userTask->getCandidateGroupsList() == BpmnTestConstants::TEST_GROUPS_LIST_XML);
    }

    public function testCandidateStarterGroups(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_XML, $this->process->getCandidateStarterGroups());
        $this->assertTrue($this->process->getCandidateStarterGroupsList() == BpmnTestConstants::TEST_GROUPS_LIST_XML);
        $this->process->setCandidateStarterGroups(BpmnTestConstants::TEST_GROUPS_API);
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_API, $this->process->getCandidateStarterGroups());
        $this->assertTrue($this->process->getCandidateStarterGroupsList() == BpmnTestConstants::TEST_GROUPS_LIST_API);
        $this->process->setCandidateStarterGroupsList(BpmnTestConstants::TEST_GROUPS_LIST_XML);
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_XML, $this->process->getCandidateStarterGroups());
        $this->assertTrue($this->process->getCandidateStarterGroupsList() == BpmnTestConstants::TEST_GROUPS_LIST_XML);
    }

    public function testCandidateStarterUsers(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_USERS_XML, $this->process->getCandidateStarterUsers());
        $this->assertTrue($this->process->getCandidateStarterUsersList() == BpmnTestConstants::TEST_USERS_LIST_XML);
        $this->process->setCandidateStarterUsers(BpmnTestConstants::TEST_USERS_API);
        $this->assertEquals(BpmnTestConstants::TEST_USERS_API, $this->process->getCandidateStarterUsers());
        $this->assertTrue($this->process->getCandidateStarterUsersList() == BpmnTestConstants::TEST_USERS_LIST_API);
        $this->process->setCandidateStarterUsersList(BpmnTestConstants::TEST_USERS_LIST_XML);
        $this->assertEquals(BpmnTestConstants::TEST_USERS_XML, $this->process->getCandidateStarterUsers());
        $this->assertTrue($this->process->getCandidateStarterUsersList() == BpmnTestConstants::TEST_USERS_LIST_XML);
    }

    public function testCandidateUsers(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_USERS_XML, $this->userTask->getCandidateUsers());
        $this->assertTrue($this->userTask->getCandidateUsersList() == BpmnTestConstants::TEST_USERS_LIST_XML);
        $this->userTask->setCandidateUsers(BpmnTestConstants::TEST_USERS_API);
        $this->assertEquals(BpmnTestConstants::TEST_USERS_API, $this->userTask->getCandidateUsers());
        $this->assertTrue($this->userTask->getCandidateUsersList() == BpmnTestConstants::TEST_USERS_LIST_API);
        $this->userTask->setCandidateUsersList(BpmnTestConstants::TEST_USERS_LIST_XML);
        $this->assertEquals(BpmnTestConstants::TEST_USERS_XML, $this->userTask->getCandidateUsers());
        $this->assertTrue($this->userTask->getCandidateUsersList() == BpmnTestConstants::TEST_USERS_LIST_XML);
    }

    public function testClass(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_XML, $this->serviceTask->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_XML, $this->messageEventDefinition->getClass());

        $this->serviceTask->setClass(BpmnTestConstants::TEST_CLASS_API);
        $this->messageEventDefinition->setClass(BpmnTestConstants::TEST_CLASS_API);

        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $this->serviceTask->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $this->messageEventDefinition->getClass());
    }

    public function testDelegateExpression(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_XML, $this->serviceTask->getDelegateExpression());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_XML, $this->messageEventDefinition->getDelegateExpression());

        $this->serviceTask->setDelegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API);
        $this->messageEventDefinition->setDelegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API);

        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API, $this->serviceTask->getDelegateExpression());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API, $this->messageEventDefinition->getDelegateExpression());
    }

    public function testDueDate(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_DUE_DATE_XML, $this->userTask->getDueDate());
        $this->userTask->setDueDate(BpmnTestConstants::TEST_DUE_DATE_API);
        $this->assertEquals(BpmnTestConstants::TEST_DUE_DATE_API, $this->userTask->getDueDate());
    }

    public function testErrorCodeVariable(): void
    {
        $errorEventDefinition = $this->startEvent->getChildElementsByType(ErrorEventDefinitionInterface::class)[0];
        $this->assertEquals("errorVariable", $errorEventDefinition->getAttributeValueNs($this->namespace, BpmnModelConstants::EXTENSION_ATTRIBUTE_ERROR_CODE_VARIABLE));
    }

    public function testErrorMessageVariable(): void
    {
        $errorEventDefinition = $this->startEvent->getChildElementsByType(ErrorEventDefinitionInterface::class)[0];
        $this->assertEquals("errorMessageVariable", $errorEventDefinition->getAttributeValueNs($this->namespace, BpmnModelConstants::EXTENSION_ATTRIBUTE_ERROR_MESSAGE_VARIABLE));
    }

    public function testErrorMessage(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->error->getErrorMessage());
        $this->error->setErrorMessage(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->error->getErrorMessage());
    }

    public function testExclusive(): void
    {
        $this->assertTrue($this->startEvent->isExclusive());
        $this->assertFalse($this->userTask->isExclusive());
        $this->userTask->setExclusive(true);
        $this->assertTrue($this->userTask->isExclusive());
        $this->assertTrue($this->parallelGateway->isExclusive());
        $this->parallelGateway->setExclusive(false);
        $this->assertFalse($this->parallelGateway->isExclusive());

        $this->assertFalse($this->callActivity->isExclusive());
        $this->callActivity->setExclusive(true);
        $this->assertTrue($this->callActivity->isExclusive());
    }

    public function testExpression(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $this->serviceTask->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $this->messageEventDefinition->getExpression());
        $this->serviceTask->setExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $this->messageEventDefinition->setExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $this->serviceTask->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $this->messageEventDefinition->getExpression());
    }

    public function testFormHandlerClass(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_XML, $this->startEvent->getFormHandlerClass());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_XML, $this->userTask->getFormHandlerClass());
        $this->startEvent->setFormHandlerClass(BpmnTestConstants::TEST_CLASS_API);
        $this->userTask->setFormHandlerClass(BpmnTestConstants::TEST_CLASS_API);
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $this->startEvent->getFormHandlerClass());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $this->userTask->getFormHandlerClass());
    }

    public function testFormKey(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->startEvent->getFormKey());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->userTask->getFormKey());
        $this->startEvent->setFormKey(BpmnTestConstants::TEST_STRING_API);
        $this->userTask->setFormKey(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->startEvent->getFormKey());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->userTask->getFormKey());
    }

    public function testInitiator(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->startEvent->getInitiator());
        $this->startEvent->setInitiator(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->startEvent->getInitiator());
    }

    public function testPriority(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_PRIORITY_XML, $this->userTask->getPriority());
        $this->userTask->setPriority(BpmnTestConstants::TEST_PRIORITY_API);
        $this->assertEquals(BpmnTestConstants::TEST_PRIORITY_API, $this->userTask->getPriority());
    }

    public function testResultVariable(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->serviceTask->getResultVariable());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->messageEventDefinition->getResultVariable());
        $this->serviceTask->setResultVariable(BpmnTestConstants::TEST_STRING_API);
        $this->messageEventDefinition->setResultVariable(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->serviceTask->getResultVariable());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->messageEventDefinition->getResultVariable());
    }

    public function testType(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_TYPE_XML, $this->serviceTask->getType());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->messageEventDefinition->getType());
        $this->serviceTask->setType(BpmnTestConstants::TEST_TYPE_API);
        $this->messageEventDefinition->setType(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_TYPE_API, $this->serviceTask->getType());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->messageEventDefinition->getType());
    }

    public function testTopic(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->serviceTask->getTopic());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $this->messageEventDefinition->getTopic());
        $this->serviceTask->setTopic(BpmnTestConstants::TEST_TYPE_API);
        $this->messageEventDefinition->setTopic(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_TYPE_API, $this->serviceTask->getTopic());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $this->messageEventDefinition->getTopic());
    }

    public function testVariableMappingClass(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_XML, $this->callActivity->getVariableMappingClass());
        $this->callActivity->setVariableMappingClass(BpmnTestConstants::TEST_CLASS_API);
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $this->callActivity->getVariableMappingClass());
    }

    public function testVariableMappingDelegateExpression(): void
    {
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_XML, $this->callActivity->getVariableMappingDelegateExpression());
        $this->callActivity->setVariableMappingDelegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API);
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API, $this->callActivity->getVariableMappingDelegateExpression());
    }

    public function testExecutionListenerExtension(): void
    {
        $processListener = $this->process->getExtensionElements()->getElementsQuery()->filterByType(ExecutionListenerInterface::class)->singleResult();
        $startEventListener = $this->startEvent->getExtensionElements()->getElementsQuery()->filterByType(ExecutionListenerInterface::class)->singleResult();
        $serviceTaskListener = $this->serviceTask->getExtensionElements()->getElementsQuery()->filterByType(ExecutionListenerInterface::class)->singleResult();
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_XML, $processListener->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_EXECUTION_EVENT_XML, $processListener->getEvent());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $startEventListener->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXECUTION_EVENT_XML, $startEventListener->getEvent());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_XML, $serviceTaskListener->getDelegateExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXECUTION_EVENT_XML, $serviceTaskListener->getEvent());
        $processListener->setClass(BpmnTestConstants::TEST_CLASS_API);
        $processListener->setEvent(BpmnTestConstants::TEST_EXECUTION_EVENT_API);
        $startEventListener->setExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $startEventListener->setEvent(BpmnTestConstants::TEST_EXECUTION_EVENT_API);
        $serviceTaskListener->setDelegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API);
        $serviceTaskListener->setEvent(BpmnTestConstants::TEST_EXECUTION_EVENT_API);
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $processListener->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_EXECUTION_EVENT_API, $processListener->getEvent());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $startEventListener->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXECUTION_EVENT_API, $startEventListener->getEvent());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API, $serviceTaskListener->getDelegateExpression());
        $this->assertEquals(BpmnTestConstants::TEST_EXECUTION_EVENT_API, $serviceTaskListener->getEvent());
    }

    public function testScriptExecutionListener(): void
    {
        $sequenceFlowListener = $this->sequenceFlow->getExtensionElements()->getElementsQuery()->filterByType(ExecutionListenerInterface::class)->singleResult();

        $script = $sequenceFlowListener->getScript();
        $this->assertEquals("groovy", $script->getScriptFormat());
        $this->assertNull($script->getResource());
        $this->assertEquals("println 'Hello World'", $script->getTextContent());

        $newScript = $this->modelInstance->newInstance(ScriptInterface::class);
        $newScript->setScriptFormat("groovy");
        $newScript->setResource("test.groovy");
        $sequenceFlowListener->setScript($newScript);

        $script = $sequenceFlowListener->getScript();
        $this->assertEquals("groovy", $script->getScriptFormat());
        $this->assertEquals("test.groovy", $script->getResource());
        $this->assertEmpty($script->getTextContent());
    }

    public function testFailedJobRetryTimeCycleExtension(): void
    {
        $timeCycle = $this->sendTask->getExtensionElements()->getElementsQuery()->filterByType(FailedJobRetryTimeCycleInterface::class)->singleResult();
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $timeCycle->getTextContent());
        $timeCycle->setTextContent(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $timeCycle->getTextContent());
    }

    public function testFieldExtension(): void
    {
        $field = $this->sendTask->getExtensionElements()->getElementsQuery()->filterByType(FieldInterface::class)->singleResult();
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $field->getName());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $field->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $field->getStringValue());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $field->getExpressionChild()->getTextContent());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $field->getString()->getTextContent());
        $field->setName(BpmnTestConstants::TEST_STRING_API);
        $field->setExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $field->setStringValue(BpmnTestConstants::TEST_STRING_API);
        $field->getExpressionChild()->setTextContent(BpmnTestConstants::TEST_EXPRESSION_API);
        $field->getString()->setTextContent(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $field->getName());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $field->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $field->getStringValue());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $field->getExpressionChild()->getTextContent());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $field->getString()->getTextContent());
    }

    public function testFormData(): void
    {
        $formData = $this->userTask->getExtensionElements()->getElementsQuery()->filterByType(FormDataInterface::class)->singleResult();
        $formField = $formData->getFormFields()[0];
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formField->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formField->getLabel());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formField->getType());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formField->getDatePattern());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formField->getDefaultValue());
        $formField->setId(BpmnTestConstants::TEST_STRING_API);
        $formField->setLabel(BpmnTestConstants::TEST_STRING_API);
        $formField->setType(BpmnTestConstants::TEST_STRING_API);
        $formField->setDatePattern(BpmnTestConstants::TEST_STRING_API);
        $formField->setDefaultValue(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formField->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formField->getLabel());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formField->getType());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formField->getDatePattern());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formField->getDefaultValue());

        $property = $formField->getProperties()->getProperties()[0];
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $property->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $property->getValue());
        $property->setId(BpmnTestConstants::TEST_STRING_API);
        $property->setValue(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $property->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $property->getValue());

        $constraint = $formField->getValidation()->getConstraints()[0];
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $constraint->getName());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $constraint->getConfig());
        $constraint->setName(BpmnTestConstants::TEST_STRING_API);
        $constraint->setConfig(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $constraint->getName());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $constraint->getConfig());

        $value = $formField->getValues()[0];
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $value->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $value->getName());
        $value->setId(BpmnTestConstants::TEST_STRING_API);
        $value->setName(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $value->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $value->getName());
    }

    public function testFormProperty(): void
    {
        $formProperty = $this->startEvent->getExtensionElements()->getElementsQuery()->filterByType(FormPropertyInterface::class)->singleResult();
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formProperty->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formProperty->getName());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formProperty->getType());
        $this->assertFalse($formProperty->isRequired());
        $this->assertTrue($formProperty->isReadable());
        $this->assertTrue($formProperty->isWriteable());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formProperty->getVariable());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $formProperty->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formProperty->getDatePattern());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $formProperty->getDefault());
        $formProperty->setId(BpmnTestConstants::TEST_STRING_API);
        $formProperty->setName(BpmnTestConstants::TEST_STRING_API);
        $formProperty->setType(BpmnTestConstants::TEST_STRING_API);
        $formProperty->setRequired(true);
        $formProperty->setReadable(false);
        $formProperty->setWriteable(false);
        $formProperty->setVariable(BpmnTestConstants::TEST_STRING_API);
        $formProperty->setExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $formProperty->setDatePattern(BpmnTestConstants::TEST_STRING_API);
        $formProperty->setDefault(BpmnTestConstants::TEST_STRING_API);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formProperty->getId());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formProperty->getName());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formProperty->getType());
        $this->assertTrue($formProperty->isRequired());
        $this->assertFalse($formProperty->isReadable());
        $this->assertFalse($formProperty->isWriteable());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formProperty->getVariable());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $formProperty->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formProperty->getDatePattern());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $formProperty->getDefault());
    }

    public function testInExtension(): void
    {
        $in = $this->callActivity->getExtensionElements()->getElementsQuery()->filterByType(InInterface::class)->singleResult();
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $in->getSource());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $in->getSourceExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $in->getVariables());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $in->getTarget());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $in->getBusinessKey());
        $this->assertTrue($in->getLocal());
        $in->setSource(BpmnTestConstants::TEST_STRING_API);
        $in->setSourceExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $in->setVariables(BpmnTestConstants::TEST_STRING_API);
        $in->setTarget(BpmnTestConstants::TEST_STRING_API);
        $in->setBusinessKey(BpmnTestConstants::TEST_EXPRESSION_API);
        $in->setLocal(false);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $in->getSource());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $in->getSourceExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $in->getVariables());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $in->getTarget());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $in->getBusinessKey());
        $this->assertFalse($in->getLocal());
    }

    public function testOutExtension(): void
    {
        $out = $this->callActivity->getExtensionElements()->getElementsQuery()->filterByType(OutInterface::class)->singleResult();
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $out->getSource());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $out->getSourceExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $out->getVariables());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $out->getTarget());
        $this->assertTrue($out->getLocal());
        $out->setSource(BpmnTestConstants::TEST_STRING_API);
        $out->setSourceExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $out->setVariables(BpmnTestConstants::TEST_STRING_API);
        $out->setTarget(BpmnTestConstants::TEST_STRING_API);
        $out->setLocal(false);
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $out->getSource());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $out->getSourceExpression());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $out->getVariables());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_API, $out->getTarget());
        $this->assertFalse($out->getLocal());
    }

    public function testPotentialStarter(): void
    {
        $potentialStarter = $this->startEvent->getExtensionElements()->getElementsQuery()->filterByType(PotentialStarterInterface::class)->singleResult();
        $expression = $potentialStarter->getResourceAssignmentExpression()->getExpression();
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_XML, $expression->getTextContent());
        $expression->setTextContent(BpmnTestConstants::TEST_GROUPS_API);
        $this->assertEquals(BpmnTestConstants::TEST_GROUPS_API, $expression->getTextContent());
    }

    public function testTaskListener(): void
    {
        $taskListener = $this->userTask->getExtensionElements()->getElementsQuery()->filterByType(TaskListenerInterface::class)->list()[0];
        $this->assertEquals(BpmnTestConstants::TEST_TASK_EVENT_XML, $taskListener->getEvent());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_XML, $taskListener->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_XML, $taskListener->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_XML, $taskListener->getDelegateExpression());
        $taskListener->setEvent(BpmnTestConstants::TEST_TASK_EVENT_API);
        $taskListener->setClass(BpmnTestConstants::TEST_CLASS_API);
        $taskListener->setExpression(BpmnTestConstants::TEST_EXPRESSION_API);
        $taskListener->setDelegateExpression(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API);
        $this->assertEquals(BpmnTestConstants::TEST_TASK_EVENT_API, $taskListener->getEvent());
        $this->assertEquals(BpmnTestConstants::TEST_CLASS_API, $taskListener->getClass());
        $this->assertEquals(BpmnTestConstants::TEST_EXPRESSION_API, $taskListener->getExpression());
        $this->assertEquals(BpmnTestConstants::TEST_DELEGATE_EXPRESSION_API, $taskListener->getDelegateExpression());

        $field = $taskListener->getFields()[0];
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $field->getName());
        $this->assertEquals(BpmnTestConstants::TEST_STRING_XML, $field->getString()->getTextContent());

        $timeouts = $taskListener->getTimeouts();
        $this->assertCount(1, $timeouts);

        $timeout = $timeouts[0];
        $this->assertNull($timeout->getTimeCycle());
        $this->assertNull($timeout->getTimeDate());
        $this->assertFalse($timeout->getTimeDuration() == null);
        $this->assertEquals("PT1H", $timeout->getTimeDuration()->getRawTextContent());
    }

    public function testScriptTaskListener(): void
    {
        $taskListener = $this->userTask->getExtensionElements()->getElementsQuery()
                        ->filterByType(TaskListenerInterface::class)->list()[1];

        $script = $taskListener->getScript();
        $this->assertEquals("groovy", $script->getScriptFormat());
        $this->assertEquals("test.groovy", $script->getResource());
        $this->assertEmpty($script->getTextContent());

        $newScript = $this->modelInstance->newInstance(ScriptInterface::class);
        $newScript->setScriptFormat("groovy");
        $newScript->setTextContent("println 'Hello World'");
        $taskListener->setScript($newScript);

        $script = $taskListener->getScript();
        $this->assertEquals("groovy", $script->getScriptFormat());
        $this->assertNull($script->getResource());
        $this->assertEquals("println 'Hello World'", $script->getTextContent());
    }

    public function testModelerProperties(): void
    {
        $properties = $this->endEvent->getExtensionElements()->getElementsQuery()
                      ->filterByType(PropertiesInterface::class)->singleResult();
        $this->assertFalse($properties == null);
        $this->assertCount(2, $properties->getProperties());

        foreach ($properties->getProperties() as $property) {
            $this->assertNull($property->getId());
            $this->assertStringStartsWith("name", $property->getName());
            $this->assertStringStartsWith("value", $property->getValue());
        }
    }

    public function testGetNonExistingCandidateUsers(): void
    {
        $this->userTask->removeAttributeNs($this->namespace, "candidateUsers");
        $this->assertNull($this->userTask->getCandidateUsers());
        $this->assertEmpty($this->userTask->getCandidateUsersList());
    }

    public function testSetNullCandidateUsers(): void
    {
        $this->assertFalse(empty($this->userTask->getCandidateUsers()));
        $this->assertFalse(empty($this->userTask->getCandidateUsersList()));
        $this->userTask->setCandidateUsers(null);
        $this->assertNull($this->userTask->getCandidateUsers());
        $this->assertEmpty($this->userTask->getCandidateUsersList());
    }

    public function testEmptyCandidateUsers(): void
    {
        $this->assertFalse(empty($this->userTask->getCandidateUsers()));
        $this->assertFalse(empty($this->userTask->getCandidateUsersList()));
        $this->userTask->setCandidateUsers("");
        $this->assertNull($this->userTask->getCandidateUsers());
        $this->assertEmpty($this->userTask->getCandidateUsersList());
    }

    public function testSetNullCandidateUsersList(): void
    {
        $this->assertFalse(empty($this->userTask->getCandidateUsers()));
        $this->assertFalse(empty($this->userTask->getCandidateUsersList()));
        $this->userTask->setCandidateUsersList([]);
        $this->assertNull($this->userTask->getCandidateUsers());
        $this->assertEmpty($this->userTask->getCandidateUsersList());
    }

    public function testEmptyCandidateUsersList(): void
    {
        $this->assertFalse(empty($this->userTask->getCandidateUsers()));
        $this->assertFalse(empty($this->userTask->getCandidateUsersList()));
        $this->userTask->setCandidateUsersList([]);
        $this->assertNull($this->userTask->getCandidateUsers());
        $this->assertEmpty($this->userTask->getCandidateUsersList());
    }

    public function testScriptResource(): void
    {
        $this->assertEquals("groovy", $this->scriptTask->getScriptFormat());
        $this->assertEquals("test.groovy", $this->scriptTask->getResource());
    }

    public function testConnector(): void
    {
        $connector = $this->serviceTask->getExtensionElements()->getElementsQuery()
                     ->filterByType(ConnectorInterface::class)->singleResult();
        $this->assertFalse($connector == null);

        $connectorId = $connector->getConnectorId();
        $this->assertFalse($connectorId == null);
        $this->assertEquals("soap-http-connector", $connectorId->getTextContent());

        $inputOutput = $connector->getInputOutput();

        $inputParameters = $inputOutput->getInputParameters();
        $this->assertCount(1, $inputParameters);

        $inputParameter = $inputParameters[0];
        $this->assertEquals("endpointUrl", $inputParameter->getName());
        $this->assertEquals("http://example.com/webservice", $inputParameter->getTextContent());

        $outputParameters = $inputOutput->getOutputParameters();
        $this->assertCount(1, $outputParameters);

        $outputParameter = $outputParameters[0];
        $this->assertEquals("result", $outputParameter->getName());
        $this->assertEquals("output", $outputParameter->getTextContent());
    }

    public function testInputOutput(): void
    {
        $inputOutput = $this->serviceTask->getExtensionElements()->getElementsQuery()
                       ->filterByType(InputOutputInterface::class)
                       ->singleResult();
        $this->assertFalse($inputOutput == null);
        $this->assertCount(6, $inputOutput->getInputParameters());
        $this->assertCount(1, $inputOutput->getOutputParameters());
    }

    public function testInputParameter(): void
    {
        // find existing
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "shouldBeConstant");

        // modify existing
        $inputParameter->setName("hello");
        $inputParameter->setTextContent("world");
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "hello");
        $this->assertEquals("world", $inputParameter->getTextContent());

        // add new one
        $inputParameter = $this->modelInstance->newInstance(InputParameterInterface::class);
        $inputParameter->setName("abc");
        $inputParameter->setTextContent("def");
        $this->serviceTask->getExtensionElements()->getElementsQuery()->filterByType(InputOutputInterface::class)
            ->singleResult()
            ->addChildElement($inputParameter);

        // search for new one
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "abc");
        $this->assertEquals("abc", $inputParameter->getName());
        $this->assertEquals("def", $inputParameter->getTextContent());
    }

    public function testaNullInputParameter(): void
    {
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "shouldBeNull");
        $this->assertEquals("shouldBeNull", $inputParameter->getName());
        $this->assertEmpty($inputParameter->getTextContent());
    }

    public function testConstantInputParameter(): void
    {
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "shouldBeConstant");
        $this->assertEquals("shouldBeConstant", $inputParameter->getName());
        $this->assertEquals("foo", $inputParameter->getTextContent());
    }

    public function testExpressionInputParameter(): void
    {
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "shouldBeExpression");
        $this->assertEquals("shouldBeExpression", $inputParameter->getName());
        $this->assertEquals('${1 + 1}', $inputParameter->getTextContent());
    }

    public function testListInputParameter(): void
    {
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "shouldBeList");
        $this->assertEquals("shouldBeList", $inputParameter->getName());
        $this->assertFalse(empty($inputParameter->getTextContent()));
        $this->assertFalse($inputParameter->getUniqueChildElementByNameNs(BpmnModelConstants::EXTENSION_NS, "list") == null);

        $list = $inputParameter->getValue();
        $this->assertCount(3, $list->getValues());
        foreach ($list->getValues() as $values) {
            $this->assertContains($values->getTextContent(), ["a", "b", "c"]);
        }

        $list = $this->modelInstance->newInstance(ListInterface::class);
        for ($i = 0; $i < 4; $i += 1) {
            $value = $this->modelInstance->newInstance(ValueInterface::class);
            $value->setTextContent("test");
            $list->add($value);
        }

        $testValues = [$this->modelInstance->newInstance(ValueInterface::class), $this->modelInstance->newInstance(ValueInterface::class)];
        $list->add($testValues[0]);
        $list->add($testValues[1]);
        $inputParameter->setValue($list);

        $list = $inputParameter->getValue();
        $this->assertCount(6, $list->getValues());
        $list->remove($testValues[0]);
        $list->remove($testValues[1]);
        $values = $list->getValues();
        $this->assertCount(4, $values);
        foreach ($values as $value) {
            $this->assertEquals("test", $value->getTextContent());
        }

        $list->remove($values[1]);
        $this->assertCount(3, $list->getValues());

        $list->remove($values[0]);
        $list->remove($values[3]);
        $this->assertCount(1, $list->getValues());

        $list->clear();
        $this->assertEmpty($list->getValues());

        // test standard list interactions
        $elements = $list->getValues();

        $value = $this->modelInstance->newInstance(ValueInterface::class);
        $list->add($value);

        $newValues = [];
        $newValues[] = $this->modelInstance->newInstance(ValueInterface::class);
        $newValues[] = $this->modelInstance->newInstance(ValueInterface::class);
        $list->add($newValues[0]);
        $list->add($newValues[1]);
        $this->assertCount(3, $list->getValues());
    }

    public function testMapInputParameter(): void
    {
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "shouldBeMap");
        $this->assertEquals("shouldBeMap", $inputParameter->getName());
        $this->assertFalse(empty($inputParameter->getTextContent()));
        $this->assertFalse($inputParameter->getUniqueChildElementByNameNs(BpmnModelConstants::EXTENSION_NS, "map") == null);

        $map = $inputParameter->getValue();
        $this->assertCount(2, $map->getEntries());
        foreach ($map->getEntries() as $entry) {
            if ($entry->getKey() == "foo") {
                $this->assertEquals("bar", $entry->getTextContent());
            } else {
                $this->assertEquals("hello", $entry->getKey());
                $this->assertEquals("world", $entry->getTextContent());
            }
        }

        $map = $this->modelInstance->newInstance(MapInterface::class);
        $entry = $this->modelInstance->newInstance(EntryInterface::class);
        $entry->setKey("test");
        $entry->setTextContent("value");
        $map->addEntry($entry);

        $inputParameter->setValue($map);
        $map = $inputParameter->getValue();
        $this->assertCount(1, $map->getEntries());
        $entry = $map->getEntries()[0];
        $this->assertEquals("test", $entry->getKey());
        $this->assertEquals("value", $entry->getTextContent());

        $inputParameter->removeValue();
        $this->assertNull($inputParameter->getValue());
    }

    public function testScriptInputParameter(): void
    {
        $inputParameter = $this->findInputParameterByName($this->serviceTask, "shouldBeScript");
        $this->assertEquals("shouldBeScript", $inputParameter->getName());
        $this->assertFalse(empty($inputParameter->getTextContent()));
        $this->assertFalse($inputParameter->getUniqueChildElementByNameNs(BpmnModelConstants::EXTENSION_NS, "script") == null);
        $this->assertFalse($inputParameter->getUniqueChildElementByType(ScriptInterface::class) == null);

        $script = $inputParameter->getValue();
        $this->assertEquals("groovy", $script->getScriptFormat());
        $this->assertNull($script->getResource());
        $this->assertEquals("1 + 1", $script->getTextContent());

        $script = $this->modelInstance->newInstance(ScriptInterface::class);
        $script->setScriptFormat("python");
        $script->setResource("script.py");

        $inputParameter->setValue($script);

        $script = $inputParameter->getValue();
        $this->assertEquals("python", $script->getScriptFormat());
        $this->assertEquals("script.py", $script->getResource());
        $this->assertEmpty($script->getTextContent());

        $inputParameter->removeValue();
        $this->assertNull($inputParameter->getValue());
    }

    public function testNestedOutputParameter(): void
    {
        $outputParameter = $this->serviceTask->getExtensionElements()->getElementsQuery()
                           ->filterByType(InputOutputInterface::class)
                           ->singleResult()->getOutputParameters()[0];

        $this->assertFalse($outputParameter == null);
        $this->assertEquals("nested", $outputParameter->getName());
        $list = $outputParameter->getValue();
        $this->assertFalse($list == null);
        $this->assertCount(2, $list->getValues());

        $values = $list->getValues();

        // nested list
        $nestedList = $values[0]->getUniqueChildElementByType(ListInterface::class);
        $this->assertFalse($nestedList == null);
        $this->assertCount(2, $nestedList->getValues());
        foreach ($nestedList->getValues() as $value) {
            $this->assertEquals("list", $value->getTextContent());
        }

        // nested map
        $nestedMap = $values[1]->getUniqueChildElementByType(MapInterface::class);
        $this->assertFalse($nestedMap == null);
        $this->assertCount(2, $nestedMap->getEntries());

        $entries = $nestedMap->getEntries();

        // nested list in nested map
        $nestedListEntry = $entries[0];
        $this->assertFalse($nestedListEntry == null);
        $this->assertEquals("list", $nestedListEntry->getKey());
        $nestedNestedList = $nestedListEntry->getValue();
        foreach ($nestedNestedList->getValues() as $value) {
            $this->assertEquals("map", $value->getTextContent());
        }

        // nested map in nested map
        $nestedMapEntry = $entries[1];
        $this->assertFalse($nestedMapEntry == null);
        $this->assertEquals("map", $nestedMapEntry->getKey());
        $nestedNestedMap = $nestedMapEntry->getValue();
        $entry = $nestedNestedMap->getEntries()[0];
        $this->assertEquals("so", $entry->getKey());
        $this->assertEquals("nested", $entry->getTextContent());
    }

    protected function findInputParameterByName(BaseElementInterface $baseElement, string $name): InputParameterInterface
    {
        $inputParameters = $baseElement->getExtensionElements()->getElementsQuery()
          ->filterByType(InputOutputInterface::class)->singleResult()->getInputParameters();
        foreach ($inputParameters as $inputParameter) {
            if ($inputParameter->getName() == $name) {
                return $inputParameter;
            }
        }
        throw new \Exception("Unable to find inputParameter");
    }

    protected function tearDown(): void
    {
        Bpmn::getInstance()->validateModel($this->modelInstance);
    }
}
