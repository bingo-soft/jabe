<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface EntryInterface extends BpmnModelElementInstanceInterface, GenericValueElementInterface
{
    public function getKey(): string;

    public function setKey(string $key): void;
}
