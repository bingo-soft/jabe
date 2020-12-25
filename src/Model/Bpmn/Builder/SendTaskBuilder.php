<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\SendTaskInterface;

class SendTaskBuilder extends AbstractSendTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SendTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, SendTaskBuilder::class);
    }
}
