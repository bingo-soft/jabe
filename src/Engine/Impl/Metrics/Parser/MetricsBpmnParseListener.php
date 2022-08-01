<?php

namespace Jabe\Engine\Impl\Metrics\Parser;

use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Impl\Bpmn\Parser\AbstractBpmnParseListener;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity
};
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};
use Jabe\Engine\Impl\Util\Xml\Element;
use Jabe\Engine\Management\Metrics;

class MetricsBpmnParseListener extends AbstractBpmnParseListener
{
    public static $ROOT_PROCESS_INSTANCE_START_COUNTER;
    public static $ACTIVITY_INSTANCE_START_COUNTER;
    public static $ACTIVITY_INSTANCE_END_COUNTER;

    public function __construct()
    {
        if (self::$ROOT_PROCESS_INSTANCE_START_COUNTER === null) {
            self::$ROOT_PROCESS_INSTANCE_START_COUNTER = new MetricsExecutionListener(
                Metrics::ROOT_PROCESS_INSTANCE_START,
                function ($delegateExecution) {
                    return $delegateExecution->getId() == $delegateExecution->getRootProcessInstanceId();
                }
            );
        }
        if (self::$ACTIVITY_INSTANCE_START_COUNTER === null) {
            self::$ACTIVITY_INSTANCE_START_COUNTER = new MetricsExecutionListener(Metrics::ACTIVTY_INSTANCE_START);
        }
        if (self::$ACTIVITY_INSTANCE_END_COUNTER === null) {
            self::$ACTIVITY_INSTANCE_END_COUNTER = new MetricsExecutionListener(Metrics::ACTIVTY_INSTANCE_END);
        }
    }

    protected function addListeners(ActivityImpl $activity): void
    {
        $activity->addBuiltInListener(ExecutionListenerInterface::EVENTNAME_START, self::$ACTIVITY_INSTANCE_START_COUNTER);
        $activity->addBuiltInListener(ExecutionListenerInterface::EVENTNAME_END, self::$ACTIVITY_INSTANCE_END_COUNTER);
    }

    public function parseProcess(Element $processElement, ProcessDefinitionEntity $processDefinition): void
    {
        $processDefinition->addBuiltInListener(ExecutionListenerInterface::EVENTNAME_START, self::$ROOT_PROCESS_INSTANCE_START_COUNTER);
    }

    public function parseStartEvent(Element $startEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseExclusiveGateway(Element $exclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseInclusiveGateway(Element $inclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseParallelGateway(Element $parallelGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseScriptTask(Element $scriptTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseServiceTask(Element $serviceTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseBusinessRuleTask(Element $businessRuleTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseTask(Element $taskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseManualTask(Element $manualTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseUserTask(Element $userTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseEndEvent(Element $endEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseSubProcess(Element $subProcessElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseCallActivity(Element $callActivityElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseSendTask(Element $sendTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseReceiveTask(Element $receiveTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseEventBasedGateway(Element $eventBasedGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseTransaction(Element $transactionElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseIntermediateThrowEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseIntermediateCatchEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseBoundaryEvent(Element $boundaryEventElement, ScopeImpl $scopeElement, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }

    public function parseMultiInstanceLoopCharacteristics(Element $activityElement, Element $multiInstanceLoopCharacteristicsElement, ActivityImpl $activity): void
    {
        $this->addListeners($activity);
    }
}
