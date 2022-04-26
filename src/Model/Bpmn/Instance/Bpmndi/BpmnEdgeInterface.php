<?php

namespace Jabe\Model\Bpmn\Instance\Bpmndi;

use Jabe\Model\Bpmn\Instance\BaseElementInterface;
use Jabe\Model\Bpmn\Instance\Di\{
    DiagramElementInterface,
    LabeledEdgeInterface
};

interface BpmnEdgeInterface extends LabeledEdgeInterface
{
    public function getBpmnElement(): BaseElementInterface;

    public function setBpmnElement(BaseElementInterface $bpmnElement): void;

    public function getSourceElement(): DiagramElementInterface;

    public function setSourceElement(DiagramElementInterface $sourceElement): void;

    public function getTargetElement(): DiagramElementInterface;

    public function setTargetElement(DiagramElementInterface $targetElement): void;

    public function getMessageVisibleKind(): ?string;

    public function setMessageVisibleKind(string $messageVisibleKind): void;

    public function getBpmnLabel(): ?BpmnLabelInterface;

    public function setBpmnLabel(BpmnLabelInterface $bpmnLabel): void;
}
