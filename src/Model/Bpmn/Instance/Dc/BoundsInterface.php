<?php

namespace BpmPlatform\Model\Bpmn\Instance\Dc;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface BoundsInterface extends BpmnModelElementInstanceInterface
{
    public function getX(): float;

    public function setX(float $x): void;

    public function getY(): float;

    public function setY(float $y): void;

    public function getWidth(): float;

    public function setWidth(float $width): void;

    public function getHeight(): float;

    public function setHeight(float $height): void;
}
