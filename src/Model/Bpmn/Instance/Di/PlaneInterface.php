<?php

namespace BpmPlatform\Model\Bpmn\Instance\Di;

interface PlaneInterface extends NodeInterface
{
    public function getDiagramElements(): array;
}
