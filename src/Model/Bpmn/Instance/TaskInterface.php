<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

interface TaskInterface extends ActivityInterface
{
    public function isAsync(): bool;

    public function setAsync(bool $isAsync): void;

    public function getDiagramElement(): BpmnShapeInterface;
}
