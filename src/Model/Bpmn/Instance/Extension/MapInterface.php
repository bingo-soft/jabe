<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface MapInterface extends BpmnModelElementInstanceInterface
{
    public function getEntries(): array;

    public function addEntry(EntryInterface $entry): void;
}
