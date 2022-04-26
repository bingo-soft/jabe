<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

interface EventInterface extends FlowNodeInterface, InteractionNodeInterface
{
    public function getProperties(): array;

    public function addProperty(PropertyInterface $property): void;

    public function getDiagramElement(): BpmnShapeInterface;
}
