<?php

namespace Jabe\Model\Bpmn\Instance\Bpmndi;

use Jabe\Model\Bpmn\Instance\Di\LabelInterface;

interface BpmnLabelInterface extends LabelInterface
{
    public function getLabelStyle(): BpmnLabelStyleInterface;

    public function setLabelStyle(BpmnLabelStyleInterface $labelStyle): void;
}
