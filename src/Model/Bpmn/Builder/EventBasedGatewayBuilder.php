<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\EventBasedGatewayInterface;

class EventBasedGatewayBuilder extends AbstractEventBasedGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EventBasedGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, EventBasedGatewayBuilder::class);
    }
}
