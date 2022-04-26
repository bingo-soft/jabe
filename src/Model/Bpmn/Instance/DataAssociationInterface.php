<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;
use Jabe\Model\Bpmn\Impl\Instance\Transformation;

interface DataAssociationInterface extends BaseElementInterface
{
    public function getSources(): array;

    public function getTarget(): ItemAwareElementInterface;

    public function setTarget(ItemAwareElementInterface $target): void;

    public function getTransformation(): FormalExpressionInterface;

    public function setTransformation(Transformation $transformation): void;

    public function getAssignments(): array;

    public function getDiagramElement(): BpmnEdgeInterface;
}
