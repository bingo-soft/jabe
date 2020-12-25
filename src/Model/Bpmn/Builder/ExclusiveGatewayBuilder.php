<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ExclusiveGatewayInterface;

class ExclusiveGatewayBuilder extends AbstractExclusiveGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ExclusiveGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, ExclusiveGatewayBuilder::class);
    }
}
