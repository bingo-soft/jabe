<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ConditionExpressionInterface,
    FlowNodeInterface,
    SequenceFlowInterface
};

abstract class AbstractSequenceFlowBuilder extends AbstractFlowElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SequenceFlowInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function from(FlowNodeInterface $source): AbstractSequenceFlowBuilder
    {
        $this->element->setSource($source);
        $source->addOutgoing($element);
        return $this->myself;
    }

    public function to(FlowNodeInterface $target): AbstractSequenceFlowBuilder
    {
        $this->element->setTarget($target);
        $target->addIncoming($element);
        return $this->myself;
    }

    public function condition(
        ConditionExpressionInterface $conditionExpression
    ): AbstractSequenceFlowBuilder {
        $this->element->setConditionExpression($conditionExpression);
        return $this->myself;
    }
}
