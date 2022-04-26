<?php

namespace Jabe\Model\Bpmn\Instance\Di;

use Jabe\Model\Bpmn\Instance\Dc\BoundsInterface;

interface ShapeInterface extends NodeInterface
{
    public function getBounds(): BoundsInterface;

    public function setBounds(BoundsInterface $bounds): void;
}
