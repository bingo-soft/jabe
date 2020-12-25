<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\SequenceFlowInterface;

class SequenceFlowBuilder extends AbstractSequenceFlowBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SequenceFlowInterface $element
    ) {
        parent::__construct($modelInstance, $element, SequenceFlowBuilder::class);
    }
}
