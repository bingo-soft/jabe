<?php

namespace BpmPlatform\Model\Bpmn\Instance\Dc;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface PointInterface extends BpmnModelElementInstanceInterface
{
    public function getX(): float;

    public function setX(float $x): void;

    public function getY(): float;

    public function setY(float $y): void;
}
