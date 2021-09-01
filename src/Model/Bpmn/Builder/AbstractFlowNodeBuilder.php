<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\AssociationDirection;
use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    AssociationInterface,
    BoundaryEventInterface,
    BusinessRuleTaskInterface,
    CallActivityInterface,
    CompensateEventDefinitionInterface,
    ConditionExpressionInterface,
    EndEventInterface,
    EventBasedGatewayInterface,
    ExclusiveGatewayInterface,
    ExecutionListenerInterface,
    FailedJobRetryTimeCycleInterface,
    FlowNodeInterface,
    GatewayInterface,
    InclusiveGatewayInterface,
    IntermediateCatchEventInterface,
    IntermediateThrowEventInterface,
    ManualTaskInterface,
    ParallelGatewayInterface,
    ReceiveTaskInterface,
    ScriptTaskInterface,
    SendTaskInterface,
    ServiceTaskInterface,
    SequenceFlowInterface,
    SubProcessInterface,
    TransactionInterface,
    UserTaskInterface
};

abstract class AbstractFlowNodeBuilder extends AbstractFlowElementBuilder
{
    private $currentSequenceFlowBuilder;
    protected $compensationStarted;
    protected $compensateBoundaryEvent;

    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        FlowNodeInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    private function getCurrentSequenceFlowBuilder(): ?SequenceFlowBuilder
    {
        if ($this->currentSequenceFlowBuilder == null) {
            $sequenceFlow = $this->createSibling(SequenceFlowInterface::class, null);
            $this->currentSequenceFlowBuilder = $sequenceFlow->builder();
        }
        return $this->currentSequenceFlowBuilder;
    }

    public function condition(?string $name, string $condition): AbstractFlowNodeBuilder
    {
        if ($name !== null) {
            $this->getCurrentSequenceFlowBuilder()->name($name);
        }
        $conditionExpression = $this->createInstance(ConditionExpressionInterface::class);
        $conditionExpression->setTextContent($condition);
        $this->getCurrentSequenceFlowBuilder()->condition($conditionExpression);
        return $this;
    }

    protected function connectTarget(FlowNodeInterface $target): void
    {
        if ($this->isBoundaryEventWithStartedCompensation()) {
            if ($target instanceof ActivityInterface) {
                $target->setForCompensation(true);
            }
            $this->connectTargetWithAssociation($target);
        } elseif ($this->isCompensationHandler()) {
            throw new BpmnModelException("Only single compensation handler allowed. " .
                                         "Call compensationDone() to continue main flow.");
        } else {
            $this->connectTargetWithSequenceFlow($target);
        }
    }

    protected function connectTargetWithSequenceFlow(FlowNodeInterface $target): void
    {
        $this->getCurrentSequenceFlowBuilder()->from($this->element)->to($target);
        $sequenceFlow = $this->getCurrentSequenceFlowBuilder()->getElement();
        $this->createEdge($sequenceFlow);
        $this->currentSequenceFlowBuilder = null;
    }

    protected function connectTargetWithAssociation(FlowNodeInterface $target): void
    {
        $association = $this->modelInstance->newInstance(AssociationInterface::class);
        $association->setTarget($target);
        $association->setSource($this->element);
        $association->setAssociationDirection(AssociationDirection::ONE);
        $this->element->getParentElement()->addChildElement($association);
        $this->createEdge($association);
    }

    public function compensationDone(): AbstractFlowNodeBuilder
    {
        if ($this->compensateBoundaryEvent != null) {
            return $this->compensateBoundaryEvent->getAttachedTo()->builder();
        } else {
            throw new BpmnModelException("No compensation in progress. Call compensationStart() first.");
        }
    }

    public function sequenceFlowId(string $sequenceFlowId): AbstractFlowNodeBuilder
    {
        $this->getCurrentSequenceFlowBuilder()->id($sequenceFlowId);
        return $this;
    }

    protected function createTarget(string $typeClass, ?string $identifier): FlowNodeInterface
    {
        $target = $this->createSibling($typeClass, $identifier);
        $targetBpmnShape = $this->createBpmnShape($target);
        $this->setCoordinates($targetBpmnShape);
        $this->connectTarget($target);
        $this->resizeSubProcess($targetBpmnShape);
        return $target;
    }

    protected function createTargetBuilder(string $typeClass, ?string $id): AbstractFlowNodeBuilder
    {
        $builder = $this->createTarget($typeClass, $id)->builder();
        if ($this->compensationStarted) {
            $builder->compensateBoundaryEvent = $compensateBoundaryEvent;
        }

        return $builder;
    }

    public function serviceTask(?string $id): ServiceTaskBuilder
    {
        $this->createTargetBuilder(ServiceTaskInterface::class, $id);
    }

    public function sendTask(?string $id): SendTaskBuilder
    {
        $this->createTargetBuilder(SendTaskInterface::class, $id);
    }

    public function userTask(?string $id): UserTaskBuilder
    {
        $this->createTargetBuilder(UserTaskInterface::class, $id);
    }

    public function businessRuleTask(?string $id): BusinessRuleTaskBuilder
    {
        $this->createTargetBuilder(BusinessRuleTaskInterface::class, $id);
    }

    public function scriptTask(?string $id): ScriptTaskBuilder
    {
        $this->createTargetBuilder(ScriptTaskInterface::class, $id);
    }

    public function receiveTask(?string $id): ReceiveTaskBuilder
    {
        $this->createTargetBuilder(ReceiveTaskInterface::class, $id);
    }

    public function manualTask(?string $id): ManualTaskBuilder
    {
        $this->createTargetBuilder(ManualTaskInterface::class, $id);
    }

    public function endEvent(?string $id): EndEventBuilder
    {
        $this->createTarget(EndEventInterface::class, $id)->builder();
    }

    public function parallelGateway(?string $id): ParallelGatewayBuilder
    {
        $this->createTarget(ParallelGatewayInterface::class, $id)->builder();
    }

    public function exclusiveGateway(?string $id): ExclusiveGatewayBuilder
    {
        $this->createTarget(ExclusiveGatewayInterface::class, $id)->builder();
    }

    public function inclusiveGateway(?string $id): InclusiveGatewayBuilder
    {
        $this->createTarget(InclusiveGatewayInterface::class, $id)->builder();
    }

    public function eventBasedGateway(?string $id): EventBasedGatewayBuilder
    {
        $this->createTarget(EventBasedGatewayInterface::class, $id)->builder();
    }

    public function intermediateCatchEvent(?string $id): IntermediateCatchEventBuilder
    {
        $this->createTarget(IntermediateCatchEventInterface::class, $id)->builder();
    }

    public function intermediateThrowEvent(?string $id): IntermediateThrowEventBuilder
    {
        $this->createTarget(IntermediateThrowEventInterface::class, $id)->builder();
    }

    public function callActivity(?string $id): CallActivityBuilder
    {
        $this->createTarget(CallActivityInterface::class, $id)->builder();
    }

    public function subProcess(?string $id): SubProcessBuilder
    {
        $this->createTarget(SubProcessInterface::class, $id)->builder();
    }

    public function transaction(?string $id): TransactionBuilder
    {
        $transaction = $this->createTarget(TransactionInterface::class, $id);
        return new TransactionBuilder($this->modelInstance, $transaction);
    }

    public function findLastGateway(): GatewayInterface
    {
        $lastGateway = $this->element;
        while (true) {
            try {
                $lastGateway = $lastGateway->getPreviousNodes()->singleResult();
                if ($lastGateway instanceof GatewayInterface) {
                    return $lastGateway;
                }
            } catch (BpmnModelException $e) {
                throw new BpmnModelException(
                    sprintf("Unable to determine an unique previous gateway of %s", $lastGateway->getId())
                );
            }
        }
        return null;
    }

    public function moveToLastGateway(): AbstractGatewayBuilder
    {
        return $this->findLastGateway()->builder();
    }

    public function moveToNode(string $identifier): AbstractFlowNodeBuilder
    {
        $instance = $this->modelInstance->getModelElementById($identifier);
        if ($instance != null && $instance instanceof FlowNodeInterface) {
            return $instance->builder();
        } else {
            throw new BpmnModelException(sprintf("Flow node not found for id %s", $identifier));
        }
    }

    public function moveToActivity(string $identifier): AbstractActivityBuilder
    {
        $instance = $this->modelInstance->getModelElementById($identifier);
        if ($instance != null && $instance instanceof ActivityInterface) {
            return $instance->builder();
        } else {
            throw new BpmnModelException(sprintf("Activity node not found for id %s", $identifier));
        }
    }

    public function connectTo(string $identifier): AbstractFlowNodeBuilder
    {
        $target = $this->modelInstance->getModelElementById($identifier);
        if ($target == null) {
            throw new BpmnModelException(
                sprintf("Unable to connect %s to element %s cause it not exists.", $element->getId(), $identifier)
            );
        } elseif (!($target instanceof FlowNodeInterface)) {
            throw new BpmnModelException(
                sprintf("Unable to connect %s to element %s cause its not a flow node.", $element->getId(), $identifier)
            );
        } else {
            $this->connectTarget($target);
            return $target->builder();
        }
    }

    public function asyncBefore(bool $asyncBefore = true): AbstractFlowNodeBuilder
    {
        $this->element->setAsyncBefore($asyncBefore);
        return $this;
    }

    public function asyncAfter(bool $asyncAfter = true): AbstractFlowNodeBuilder
    {
        $this->element->setAsyncAfter($asyncAfter);
        return $this;
    }

    public function notExclusive(): AbstractFlowNodeBuilder
    {
        $this->element->setExclusive(false);
        return $this;
    }

    public function exclusive(bool $exclusive): AbstractFlowNodeBuilder
    {
        $this->element->setExclusive($exclusive);
        return $this;
    }

    public function camundaJobPriority(string $jobPriority): AbstractFlowNodeBuilder
    {
        $this->element->setJobPriority($jobPriority);
        return $this;
    }

    public function failedJobRetryTimeCycle(string $retryTimeCycle): AbstractFlowNodeBuilder
    {
        $failedJobRetryTimeCycle = $this->createInstance(FailedJobRetryTimeCycleInterface::class);
        $failedJobRetryTimeCycle->setTextContent($retryTimeCycle);
        $this->addExtensionElement($failedJobRetryTimeCycle);
        return $this;
    }

    public function executionListenerClass(string $eventName, string $className): AbstractFlowNodeBuilder
    {
        $executionListener = $this->createInstance(ExecutionListenerInterface::class);
        $executionListener->setEvent($eventName);
        $executionListener->setClass($className);
        $this->addExtensionElement($executionListener);
        return $this;
    }

    public function executionListenerExpression(string $eventName, string $expression): AbstractFlowNodeBuilder
    {
        $executionListener = $this->createInstance(ExecutionListenerInterface::class);
        $executionListener->setEvent($eventName);
        $executionListener->setExpression($expression);
        $this->addExtensionElement($executionListener);
        return $this;
    }

    public function executionListenerDelegateExpression(
        string $eventName,
        string $delegateExpression
    ): AbstractFlowNodeBuilder {
        $executionListener = $this->createInstance(ExecutionListenerInterface::class);
        $executionListener->setEvent($eventName);
        $executionListener->setDelegateExpression($delegateExpression);
        $this->addExtensionElement($executionListener);
        return $this;
    }

    public function compensationStart(): AbstractFlowNodeBuilder
    {
        if ($this->element instanceof BoundaryEventInterface) {
            $boundaryEvent = $this->element;
            foreach ($boundaryEvent->getEventDefinitions() as $eventDefinition) {
                if ($eventDefinition instanceof CompensateEventDefinitionInterface) {
                    $this->compensateBoundaryEvent = $boundaryEvent;
                    $this->compensationStarted = true;
                    return $this;
                }
            }
        }

        throw new BpmnModelException("Compensation can only be started on a boundary event " .
                                     "with a compensation event definition");
    }

    public function isBoundaryEventWithStartedCompensation(): bool
    {
        return $this->compensationStarted && $this->compensateBoundaryEvent != null;
    }

    public function isCompensationHandler(): bool
    {
        return !$this->compensationStarted && $this->compensateBoundaryEvent != null;
    }
}
