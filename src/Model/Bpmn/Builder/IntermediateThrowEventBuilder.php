<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\IntermediateThrowEventInterface;

class IntermediateThrowEventBuilder extends AbstractIntermediateThrowEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        IntermediateThrowEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, IntermediateThrowEventBuilder::class);
    }
}
