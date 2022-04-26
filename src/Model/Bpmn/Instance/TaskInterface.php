<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

interface TaskInterface extends ActivityInterface
{
    public function isAsync(): bool;

    public function setAsync(bool $isAsync): void;

    public function getDiagramElement(): BpmnShapeInterface;
}
