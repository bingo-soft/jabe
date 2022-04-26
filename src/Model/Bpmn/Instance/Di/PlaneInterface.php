<?php

namespace Jabe\Model\Bpmn\Instance\Di;

interface PlaneInterface extends NodeInterface
{
    public function getDiagramElements(): array;

    public function addDiagramElement(DiagramElementInterface $element): void;
}
