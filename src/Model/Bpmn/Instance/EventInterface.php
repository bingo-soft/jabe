<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

interface EventInterface extends FlowNodeInterface, InteractionNodeInterface
{
    public function getProperties(): array;

    public function addProperty(PropertyInterface $property): void;

    public function getDiagramElement(): BpmnShapeInterface;
}
