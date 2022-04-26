<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\SequenceFlowBuilder;
use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

interface SequenceFlowInterface extends FlowElementInterface
{
    public function builder(): SequenceFlowBuilder;

    public function getSource(): FlowNodeInterface;

    public function setSource(FlowNodeInterface $source): void;

    public function getTarget(): FlowNodeInterface;

    public function setTarget(FlowNodeInterface $target): void;

    public function isImmediate(): bool;

    public function setImmediate(bool $isImmediate): void;

    public function getConditionExpression(): ConditionExpressionInterface;

    public function setConditionExpression(ConditionExpressionInterface $conditionExpression): void;

    public function removeConditionExpression(): void;

    public function getDiagramElement(): ?BpmnEdgeInterface;
}
