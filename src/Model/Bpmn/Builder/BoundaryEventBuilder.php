<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\BoundaryEventInterface;

class BoundaryEventBuilder extends AbstractBoundaryEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BoundaryEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, BoundaryEventBuilder::class);
    }
}
