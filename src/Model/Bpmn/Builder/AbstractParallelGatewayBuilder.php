<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ParallelGatewayInterface,
    SequenceFlowInterface
};

abstract class AbstractParallelGatewayBuilder extends AbstractGatewayBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ParallelGatewayInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }
}
