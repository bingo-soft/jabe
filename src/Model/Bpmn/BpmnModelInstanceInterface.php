<?php

namespace Jabe\Model\Bpmn;

use Jabe\Model\Bpmn\Instance\DefinitionsInterface;
use Jabe\Model\Xml\ModelInstanceInterface;

interface BpmnModelInstanceInterface extends ModelInstanceInterface
{
    public function getDefinitions(): ?DefinitionsInterface;

    public function setDefinitions(DefinitionsInterface $definitions): void;

    public function clone(): BpmnModelInstanceInterface;
}
