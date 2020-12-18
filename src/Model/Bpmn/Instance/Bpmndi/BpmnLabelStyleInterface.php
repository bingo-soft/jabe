<?php

namespace BpmPlatform\Model\Bpmn\Instance\Bpmndi;

use BpmPlatform\Model\Bpmn\Instance\Dc\FontInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\StyleInterface;

interface BpmnLabelStyleInterface extends StyleInterface
{
    public function getFont(): FontInterface;

    public function setFont(FontInterface $font): void;
}
