<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

use Jabe\Engine\Impl\Bpmn\Behavior\MultiInstanceActivityBehavior;
use Jabe\Engine\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Model\PropertyKey;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};
use Jabe\Engine\Impl\Util\ParseUtil;
use Jabe\Engine\Impl\Util\Xml\Element;

class DefaultFailedJobParseListener extends AbstractBpmnParseListener
{
    protected const TYPE = "type";
    protected const START_TIMER_EVENT = "startTimerEvent";
    protected const BOUNDARY_TIMER = "boundaryTimer";
    protected const INTERMEDIATE_SIGNAL_THROW = "intermediateSignalThrow";
    protected const INTERMEDIATE_TIMER = "intermediateTimer";
    protected const SIGNAL_EVENT_DEFINITION = "signalEventDefinition";
    protected const MULTI_INSTANCE_LOOP_CHARACTERISTICS = "multiInstanceLoopCharacteristics";
    protected const EXTENSION_ELEMENTS = "extensionElements";
    protected const FAILED_JOB_RETRY_TIME_CYCLE = "failedJobRetryTimeCycle";

    public const ENGINE_NS = "http://test.org/schema/1.0/bpmn";

    public static $FAILED_JOB_CONFIGURATION;

    public function __construct()
    {
        if (self::$FAILED_JOB_CONFIGURATION === null) {
            self::$FAILED_JOB_CONFIGURATION = new PropertyKey("FAILED_JOB_CONFIGURATION");
        }
    }

    public function parseStartEvent(Element $startEventElement, ScopeImpl $scope, ActivityImpl $startEventActivity): void
    {
        $type = $startEventActivity->getProperties()->get(BpmnProperties::type());
        if ($type !== null && $type == self::START_TIMER_EVENT || $this->isAsync($startEventActivity)) {
            $this->setFailedJobRetryTimeCycleValue($startEventElement, $startEventActivity);
        }
    }

    public function parseBoundaryEvent(Element $boundaryEventElement, ScopeImpl $scopeElement, ActivityImpl $nestedActivity): void
    {
        $type = $nestedActivity->getProperties()->get(BpmnProperties::type());
        if (($type !== null && $type == self::BOUNDARY_TIMER) || $this->isAsync($nestedActivity)) {
            $this->setFailedJobRetryTimeCycleValue($boundaryEventElement, $nestedActivity);
        }
    }

    public function parseIntermediateThrowEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $type = $activity->getProperties()->get(BpmnProperties::type());
        if ($type !== null) {
            $this->setFailedJobRetryTimeCycleValue($intermediateEventElement, $activity);
        }
    }

    public function parseIntermediateCatchEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $type = $activity->getProperties()->get(BpmnProperties::type());
        if ($type !== null && $type == self::INTERMEDIATE_TIMER || $this->isAsync($activity)) {
            $this->setFailedJobRetryTimeCycleValue($intermediateEventElement, $activity);
        }
    }

    public function parseEndEvent(Element $endEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($endEventElement, $activity);
    }

    public function parseExclusiveGateway(Element $exclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($exclusiveGwElement, $activity);
    }

    public function parseInclusiveGateway(Element $exclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($exclusiveGwElement, $activity);
    }

    public function parseEventBasedGateway(Element $exclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($exclusiveGwElement, $activity);
    }

    public function parseParallelGateway(Element $exclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($exclusiveGwElement, $activity);
    }

    public function parseScriptTask(Element $scriptTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity(scriptTaskElement, activity);
    }

    public function parseServiceTask(Element $serviceTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($serviceTaskElement, $activity);
    }

    public function parseBusinessRuleTask(Element $businessRuleTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($businessRuleTaskElement, $activity);
    }

    public function parseTask(Element $taskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($taskElement, $activity);
    }

    public function parseUserTask(Element $userTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($userTaskElement, $activity);
    }

    public function parseCallActivity(Element $callActivityElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($callActivityElement, $activity);
    }

    public function parseReceiveTask(Element $receiveTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($receiveTaskElement, $activity);
    }

    public function parseSendTask(Element $sendTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($sendTaskElement, $activity);
    }

    public function parseSubProcess(Element $subProcessElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($subProcessElement, $activity);
    }

    public function parseTransaction(Element $transactionElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
        $this->parseActivity($transactionElement, $activity);
    }

    protected function isAsync(ActivityImpl $activity): bool
    {
        return $activity->isAsyncBefore() || $activity->isAsyncAfter();
    }

    protected function parseActivity(Element $element, ActivityImpl $activity): void
    {
        if ($this->isMultiInstance($activity)) {
            // in case of multi-instance, the extension elements is set according to the async attributes
            // the extension for multi-instance body is set on the element of the activity
            $miBody = $activity->getParentFlowScopeActivity();
            if ($this->isAsync($miBody)) {
                $this->setFailedJobRetryTimeCycleValue($element, $miBody);
            }
            // the extension for inner activity is set on the multiInstanceLoopCharacteristics element
            if ($this->isAsync($activity)) {
                $multiInstanceLoopCharacteristics = $element->element(self::MULTI_INSTANCE_LOOP_CHARACTERISTICS);
                $this->setFailedJobRetryTimeCycleValue($multiInstanceLoopCharacteristics, $activity);
            }
        } elseif ($this->isAsync($activity)) {
            $this->setFailedJobRetryTimeCycleValue($element, $activity);
        }
    }

    protected function setFailedJobRetryTimeCycleValue(Element $element, ActivityImpl $activity): void
    {
        $failedJobRetryTimeCycleConfiguration = null;

        $extensionElements = $element->element(self::EXTENSION_ELEMENTS);
        if (!empty($extensionElements)) {
            $failedJobRetryTimeCycleElement = $extensionElements->elementNS(self::ENGINE_NS, self::FAILED_JOB_RETRY_TIME_CYCLE);
            if ($failedJobRetryTimeCycleElement === null) {
                // try to get it from the activiti namespace
                $failedJobRetryTimeCycleElement = $extensionElements->elementNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, self::FAILED_JOB_RETRY_TIME_CYCLE);
            }

            if (!empty($failedJobRetryTimeCycleElement)) {
                $failedJobRetryTimeCycleConfiguration = $failedJobRetryTimeCycleElement->getText();
            }
        }

        if (empty($failedJobRetryTimeCycleConfiguration)) {
            $failedJobRetryTimeCycleConfiguration = Context::getProcessEngineConfiguration()->getFailedJobRetryTimeCycle();
        }

        if ($failedJobRetryTimeCycleConfiguration !== null) {
            $configuration = ParseUtil::parseRetryIntervals($failedJobRetryTimeCycleConfiguration);
            $activity->getProperties()->set(self::FAILED_JOB_CONFIGURATION, $configuration);
        }
    }

    protected function isMultiInstance(ActivityImpl $activity): bool
    {
        // #isMultiInstance() don't work since the property is not set yet
        $parent = $activity->getParentFlowScopeActivity();
        return $parent !== null && $parent->getActivityBehavior() instanceof MultiInstanceActivityBehavior;
    }
}
