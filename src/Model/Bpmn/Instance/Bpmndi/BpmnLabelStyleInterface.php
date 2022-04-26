<?php

namespace Jabe\Model\Bpmn\Instance\Bpmndi;

use Jabe\Model\Bpmn\Instance\Dc\FontInterface;
use Jabe\Model\Bpmn\Instance\Di\StyleInterface;

interface BpmnLabelStyleInterface extends StyleInterface
{
    public function getFont(): FontInterface;

    public function setFont(FontInterface $font): void;
}
