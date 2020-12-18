<?php

namespace BpmPlatform\Model\Bpmn\Instance\Bpmndi;

use BpmPlatform\Model\Bpmn\Instance\Di\LabelInterface;

interface BpmnLabelInterface extends LabelInterface
{
    public function getLabelStyle(): BpmnLabelStyleInterface;

    public function setLabelStyle(BpmnLabelStyleInterface $labelStyle): void;
}
