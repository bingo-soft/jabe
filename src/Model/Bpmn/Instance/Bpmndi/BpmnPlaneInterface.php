<?php

namespace BpmPlatform\Model\Bpmn\Instance\Bpmndi;

use BpmPlatform\Model\Bpmn\Instance\BaseElementInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\PlaneInterface;

interface BpmnPlaneInterface extends PlaneInterface
{
    public function getBpmnElement(): BaseElementInterface;

    public function setBpmnElement(BaseElementInterface $bpmnElement): void;
}
