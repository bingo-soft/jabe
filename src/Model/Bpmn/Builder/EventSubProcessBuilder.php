<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\EventSubProcessInterface;

class EventSubProcessBuilder extends AbstractEventSubProcessBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EventSubProcessInterface $element
    ) {
        parent::__construct($modelInstance, $element, EventSubProcessBuilder::class);
    }
}
