<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface EntryInterface extends BpmnModelElementInstanceInterface, GenericValueElementInterface
{
    public function getKey(): string;

    public function setKey(string $key): void;
}
