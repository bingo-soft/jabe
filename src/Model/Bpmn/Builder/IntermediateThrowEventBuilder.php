<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\IntermediateThrowEventInterface;

class IntermediateThrowEventBuilder extends AbstractIntermediateThrowEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        IntermediateThrowEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, IntermediateThrowEventBuilder::class);
    }
}
