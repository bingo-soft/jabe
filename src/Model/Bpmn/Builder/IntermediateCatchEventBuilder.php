<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\IntermediateCatchEventInterface;

class IntermediateCatchEventBuilder extends AbstractIntermediateCatchEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        IntermediateCatchEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, IntermediateCatchEventBuilder::class);
    }
}
