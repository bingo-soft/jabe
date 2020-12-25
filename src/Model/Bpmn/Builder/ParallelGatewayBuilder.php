<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ParallelGatewayInterface;

class ParallelGatewayBuilder extends AbstractParallelGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ParallelGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, ParallelGatewayBuilder::class);
    }
}
