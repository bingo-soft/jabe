<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ReceiveTaskInterface;

class ReceiveTaskBuilder extends AbstractReceiveTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ReceiveTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ReceiveTaskBuilder::class);
    }
}
