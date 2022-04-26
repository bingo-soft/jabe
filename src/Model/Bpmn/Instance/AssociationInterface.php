<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

interface AssociationInterface extends ArtifactInterface
{
    public function getSource(): BaseElementInterface;

    public function setSource(BaseElementInterface $source): void;

    public function getTarget(): BaseElementInterface;

    public function setTarget(BaseElementInterface $target): void;

    public function getAssociationDirection(): string;

    public function setAssociationDirection(string $associationDirection): void;

    public function getDiagramElement(): BpmnEdgeInterface;
}
