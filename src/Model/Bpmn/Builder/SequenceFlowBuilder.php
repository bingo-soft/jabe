<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\SequenceFlowInterface;

class SequenceFlowBuilder extends AbstractSequenceFlowBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SequenceFlowInterface $element
    ) {
        parent::__construct($modelInstance, $element, SequenceFlowBuilder::class);
    }
}
