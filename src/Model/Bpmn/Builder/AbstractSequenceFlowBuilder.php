<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
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
        $source->addOutgoing($this->element);
        return $this;
    }

    public function to(FlowNodeInterface $target): AbstractSequenceFlowBuilder
    {
        $this->element->setTarget($target);
        $target->addIncoming($this->element);
        return $this;
    }

    public function condition(
        ConditionExpressionInterface $conditionExpression
    ): AbstractSequenceFlowBuilder {
        $this->element->setConditionExpression($conditionExpression);
        return $this;
    }
}
