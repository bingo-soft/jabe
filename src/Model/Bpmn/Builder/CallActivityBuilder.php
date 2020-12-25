<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\CallActivityInterface;

class CallActivityBuilder extends AbstractCallActivityBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        CallActivityInterface $element
    ) {
        parent::__construct($modelInstance, $element, CallActivityBuilder::class);
    }
}
