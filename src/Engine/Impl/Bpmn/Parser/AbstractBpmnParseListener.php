<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

use BpmPlatform\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use BpmPlatform\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl,
    TransitionImpl
};
use BpmPlatform\Engine\Impl\Util\Xml\Element;
use BpmPlatform\Engine\Impl\Variable\VariableDeclaration;

class AbstractBpmnParseListener implements BpmnParseListener
{
    public function parseProcess(Element $processElement, ProcessDefinitionEntity $processDefinition): void
    {
    }

    public function parseStartEvent(Element $startEventElement, ScopeImpl $scope, ActivityImpl $startEventActivity): void
    {
    }

    public function parseExclusiveGateway(Element $exclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseInclusiveGateway(Element $inclusiveGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseParallelGateway(Element $parallelGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseScriptTask(Element $scriptTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseServiceTask(Element $serviceTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseBusinessRuleTask(Element $businessRuleTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseTask(Element $taskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseManualTask(Element $manualTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseUserTask(Element $userTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseEndEvent(Element $endEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseBoundaryTimerEventDefinition(Element $timerEventDefinition, bool $interrupting, ActivityImpl $timerActivity): void
    {
    }

    public function parseBoundaryErrorEventDefinition(Element $errorEventDefinition, bool $interrupting, ActivityImpl $activity, ActivityImpl $nestedErrorEventActivity): void
    {
    }

    public function parseSubProcess(Element $subProcessElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseCallActivity(Element $callActivityElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseProperty(Element $propertyElement, VariableDeclaration $variableDeclaration, ActivityImpl $activity): void
    {
    }

    public function parseSequenceFlow(Element $sequenceFlowElement, ScopeImpl $scopeElement, TransitionImpl $transition): void
    {
    }

    public function parseSendTask(Element $sendTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseMultiInstanceLoopCharacteristics(Element $activityElement, Element $multiInstanceLoopCharacteristicsElement, ActivityImpl $activity): void
    {
    }

    public function parseIntermediateTimerEventDefinition(Element $timerEventDefinition, ActivityImpl $timerActivity): void
    {
    }

    public function parseRootElement(Element $rootElement, array $processDefinitions): void
    {
    }

    public function parseReceiveTask(Element $receiveTaskElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseIntermediateSignalCatchEventDefinition(Element $signalEventDefinition, ActivityImpl $signalActivity): void
    {
    }

    public function parseBoundarySignalEventDefinition(Element $signalEventDefinition, bool $interrupting, ActivityImpl $signalActivity): void
    {
    }

    public function parseEventBasedGateway(Element $eventBasedGwElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseTransaction(Element $transactionElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseCompensateEventDefinition(Element $compensateEventDefinition, ActivityImpl $compensationActivity): void
    {
    }

    public function parseIntermediateThrowEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseIntermediateCatchEvent(Element $intermediateEventElement, ScopeImpl $scope, ActivityImpl $activity): void
    {
    }

    public function parseBoundaryEvent(Element $boundaryEventElement, ScopeImpl $scopeElement, ActivityImpl $nestedActivity): void
    {
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
}
