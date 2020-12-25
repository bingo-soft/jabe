<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\InclusiveGatewayInterface;

class InclusiveGatewayBuilder extends AbstractInclusiveGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        InclusiveGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, InclusiveGatewayBuilder::class);
    }
}
