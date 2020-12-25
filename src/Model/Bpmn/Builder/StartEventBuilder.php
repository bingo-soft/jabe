<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\StartEventInterface;

class StartEventBuilder extends AbstractStartEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        StartEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, StartEventBuilder::class);
    }
}
