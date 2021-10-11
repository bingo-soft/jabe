<?php

namespace BpmPlatform\Model\Bpmn;

use BpmPlatform\Model\Bpmn\Instance\DefinitionsInterface;
use BpmPlatform\Model\Xml\ModelInstanceInterface;

interface BpmnModelInstanceInterface extends ModelInstanceInterface
{
    public function getDefinitions(): ?DefinitionsInterface;

    public function setDefinitions(DefinitionsInterface $definitions): void;

    public function clone(): BpmnModelInstanceInterface;
}
