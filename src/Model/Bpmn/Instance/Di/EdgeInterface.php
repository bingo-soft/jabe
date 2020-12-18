<?php

namespace BpmPlatform\Model\Bpmn\Instance\Di;

interface EdgeInterface extends DiagramElementInterface
{
    public function getWaypoints(): array;
}
