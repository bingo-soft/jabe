<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\EventSubProcessInterface;

class EventSubProcessBuilder extends AbstractEventSubProcessBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EventSubProcessInterface $element
    ) {
        parent::__construct($modelInstance, $element, EventSubProcessBuilder::class);
    }
}
