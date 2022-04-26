<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ParallelGatewayInterface;

class ParallelGatewayBuilder extends AbstractParallelGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ParallelGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, ParallelGatewayBuilder::class);
    }
}
