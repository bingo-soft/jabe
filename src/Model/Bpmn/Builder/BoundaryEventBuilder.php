<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\BoundaryEventInterface;

class BoundaryEventBuilder extends AbstractBoundaryEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BoundaryEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, BoundaryEventBuilder::class);
    }
}
