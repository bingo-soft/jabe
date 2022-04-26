<?php

namespace Jabe\Model\Bpmn\Instance\Bpmndi;

use Jabe\Model\Bpmn\Instance\BaseElementInterface;
use Jabe\Model\Bpmn\Instance\Di\PlaneInterface;

interface BpmnPlaneInterface extends PlaneInterface
{
    public function getBpmnElement(): BaseElementInterface;

    public function setBpmnElement(BaseElementInterface $bpmnElement): void;
}
