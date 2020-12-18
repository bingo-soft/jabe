<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

interface GroupInterface extends ArtifactInterface
{
    public function getCategory(): CategoryValueInterface;

    public function setCategory(CategoryValueInterface $categoryValue): void;

    public function getDiagramElement(): BpmnEdgeInterface;
}
