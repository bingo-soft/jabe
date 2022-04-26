<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

interface GroupInterface extends ArtifactInterface
{
    public function getCategory(): CategoryValueInterface;

    public function setCategory(CategoryValueInterface $categoryValue): void;

    public function getDiagramElement(): BpmnEdgeInterface;
}
