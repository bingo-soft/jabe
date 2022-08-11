<?php

namespace Jabe\Engine\Impl\History\Parser;

use Jabe\Engine\Delegate\{
    ExecutionListenerInterface,
    TaskListenerInterface
};
use Jabe\Engine\Impl\Bpmn\Behavior\UserTaskActivityBehavior;
use Jabe\Engine\Impl\Bpmn\Parser\BpmnParseListenerInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\History\HistoryLevel;
use Jabe\Engine\Impl\History\Event\HistoryEventTypes;
use Jabe\Engine\Impl\History\Handler\HistoryEventHandlerInterface;
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Engine\Impl\Pvm\PvmEvent;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl,
    TransitionImpl
};
use Jabe\Engine\Impl\Task\TaskDefinition;
use Sax\Element;
use Jabe\Engine\Impl\Variable\VariableDeclaration;

class HistoryParseListener implements BpmnParseListenerInterface
{
    // Cached listeners
    // listeners can be reused for a given process engine instance but cannot be cached in static fields since
    // different process engine instances on the same Classloader may have different HistoryEventProducer
    // configurations wired
    protected $PROCESS_INSTANCE_START_LISTENER;
    protected $PROCESS_INSTANCE_END_LISTENER;

    protected $ACTIVITY_INSTANCE_START_LISTENER;
    protected $ACTIVITY_INSTANCE_END_LISTENER;

    protected $USER_TASK_ASSIGNMENT_HANDLER;
    protected $USER_TASK_ID_HANDLER;

    // The history level set in the process engine configuration
    protected $historyLevel;

    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        $this->initExecutionListeners($historyEventProducer);
    }

    protected function initExecutionListeners(HistoryEventProducerInterface $historyEventProducer): void
    {
        $this->PROCESS_INSTANCE_START_LISTENER = new ProcessInstanceStartListener($historyEventProducer);
        $this->PROCESS_INSTANCE_END_LISTENER = new ProcessInstanceEndListener($historyEventProducer);

        $this->ACTIVITY_INSTANCE_START_LISTENER = new ActivityInstanceStartListener($historyEventProducer);
        $this->ACTIVITY_INSTANCE_END_LISTENER = new ActivityInstanceEndListener($historyEventProducer);

        $this->USER_TASK_ASSIGNMENT_HANDLER = new ActivityInstanceUpdateListener($historyEventProducer);
        $this->USER_TASK_ID_HANDLER = $this->USER_TASK_ASSIGNMENT_HANDLER;
    }

    public function parseProcess(Element $processElement, ProcessDefinitionEntity $processDefinition): void
    {
        $this->ensureHistoryLevelInitialized();
        if ($this->historyLevel->isHistoryEventProduced(HistoryEventTypes::processInstanceEnd(), null)) {
            $processDefinition->addBuiltInListener(PvmEvent::EVENTNAME_END, $this->PROCESS_INSTANCE_END_LISTENER);
        }
    }

    public function parseExclusiveGateway(Element $exclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseInclusiveGateway(Element $inclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseCallActivity(Element $callActivityElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseManualTask(Element $manualTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseReceiveTask(Element $receiveTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseScriptTask(Element $scriptTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseTask(Element $taskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseUserTask(Element $userTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->ensureHistoryLevelInitialized();
        $this->addActivityHandlers($activity);

        if ($this->historyLevel->isHistoryEventProduced(HistoryEventTypes::taskInstanceCreate(), null)) {
            $taskDefinition = $activity->getActivityBehavior()->getTaskDefinition();
            $taskDefinition->addBuiltInTaskListener(TaskListenerInterface::EVENTNAME_ASSIGNMENT, $this->USER_TASK_ASSIGNMENT_HANDLER);
            $taskDefinition->addBuiltInTaskListener(TaskListenerInterface::EVENTNAME_CREATE, $this->USER_TASK_ID_HANDLER);
        }
    }

    public function parseServiceTask(Element $serviceTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseBusinessRuleTask(Element $businessRuleTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseSubProcess(Element $subProcessElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseStartEvent(Element $startEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseSendTask(Element $sendTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseEndEvent(Element $endEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseParallelGateway(Element $parallelGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseBoundaryTimerEventDefinition(Element $timerEventDefinition, bool $interrupting, ActivityImpl $timerActivity): void
    {
    }

    public function parseBoundaryErrorEventDefinition(Element $errorEventDefinition, bool $interrupting, ActivityImpl $activity, ActivityImpl $nestedErrorEventActivity): void
    {
    }

    public function parseIntermediateTimerEventDefinition(Element $timerEventDefinition, ActivityImpl $timerActivity): void
    {
    }

    public function parseProperty(Element $propertyElement, VariableDeclaration $variableDeclaration, ActivityImpl $activity): void
    {
    }

    public function parseSequenceFlow(Element $sequenceFlowElement, ScopeImpl $scopeElement, TransitionImpl $transition): void
    {
    }

    public function parseRootElement(Element $rootElement, array $processDefinitions): void
    {
    }

    public function parseBoundarySignalEventDefinition(Element $signalEventDefinition, bool $interrupting, ActivityImpl $signalActivity): void
    {
    }

    public function parseEventBasedGateway(Element $eventBasedGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseMultiInstanceLoopCharacteristics(
        Element $activityElement,
        Element $multiInstanceLoopCharacteristicsElement,
        ActivityImpl $activity
    ): void {
        $this->addActivityHandlers($activity);
    }

    public function parseIntermediateSignalCatchEventDefinition(Element $signalEventDefinition, ActivityImpl $signalActivity): void
    {
    }

    public function parseTransaction(Element $transactionElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseCompensateEventDefinition(Element $compensateEventDefinition, ActivityImpl $compensationActivity): void
    {
    }

    public function parseIntermediateThrowEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseIntermediateCatchEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        // do not write history for link events
        if ($activity->getProperty("type") != "intermediateLinkCatch") {
            $this->addActivityHandlers($activity);
        }
    }

    public function parseBoundaryEvent(Element $boundaryEventElement, ScopeImpl $scopeElement, ActivityImpl $activity): void
    {
        $this->addActivityHandlers($activity);
    }

    public function parseIntermediateMessageCatchEventDefinition(Element $messageEventDefinition, ActivityImpl $nestedActivity): void
    {
    }

    public function parseBoundaryMessageEventDefinition(Element $element, bool $interrupting, ActivityImpl $messageActivity): void
    {
    }

    public function parseBoundaryEscalationEventDefinition(Element $escalationEventDefinition, bool $interrupting, ActivityImpl $boundaryEventActivity): void
    {
    }

    public function parseBoundaryConditionalEventDefinition(Element $element, bool $interrupting, ActivityImpl $conditionalActivity): void
    {
    }

    public function parseIntermediateConditionalEventDefinition(Element $conditionalEventDefinition, ActivityImpl $conditionalActivity): void
    {
    }

    public function parseConditionalStartEventForEventSubprocess(Element $element, ActivityImpl $conditionalActivity, bool $interrupting): void
    {
    }

    // helper methods ///////////////////////////////////////////////////////////

    protected function addActivityHandlers(ActivityImpl $activity): void
    {
        $this->ensureHistoryLevelInitialized();
        if ($this->historyLevel->isHistoryEventProduced(HistoryEventTypes::activityInstanceStart(), null)) {
            $activity->addBuiltInListener(PvmEvent::EVENTNAME_START, $this->ACTIVITY_INSTANCE_START_LISTENER, 0);
        }
        if ($this->historyLevel->isHistoryEventProduced(HistoryEventTypes::activityInstanceEnd(), null)) {
            $activity->addBuiltInListener(PvmEvent::EVENTNAME_END, $this->ACTIVITY_INSTANCE_END_LISTENER);
        }
    }

    protected function ensureHistoryLevelInitialized(): void
    {
        if ($this->historyLevel === null) {
            $this->historyLevel = Context::getProcessEngineConfiguration()->getHistoryLevel();
        }
    }
}
