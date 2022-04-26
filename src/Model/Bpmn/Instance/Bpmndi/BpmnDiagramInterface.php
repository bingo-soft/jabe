<?php

namespace Jabe\Model\Bpmn\Instance\Bpmndi;

use Jabe\Model\Bpmn\Instance\Di\DiagramInterface;

interface BpmnDiagramInterface extends DiagramInterface
{
    public function getBpmnPlane(): BpmnPlaneInterface;

    public function setBpmnPlane(BpmnPlaneInterface $bpmnPlane): void;

    public function getBpmnLabelStyles(): array;
}
