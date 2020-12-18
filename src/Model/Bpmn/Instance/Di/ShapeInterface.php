<?php

namespace BpmPlatform\Model\Bpmn\Instance\Di;

use BpmPlatform\Model\Bpmn\Instance\Dc\BoundsInterface;

interface ShapeInterface extends NodeInterface
{
    public function getBound(): BoundsInterface;

    public function setBounds(BoundsInterface $bounds): void;
}
