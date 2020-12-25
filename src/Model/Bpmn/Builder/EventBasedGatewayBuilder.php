<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\EventBasedGatewayInterface;

class EventBasedGatewayBuilder extends AbstractEventBasedGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EventBasedGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, EventBasedGatewayBuilder::class);
    }
}
