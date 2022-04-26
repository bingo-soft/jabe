<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\SendTaskInterface;

class SendTaskBuilder extends AbstractSendTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SendTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, SendTaskBuilder::class);
    }
}
