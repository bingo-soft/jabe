<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ComplexGatewayInterface;

class ComplexGatewayBuilder extends AbstractComplexGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ComplexGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, ComplexGatewayBuilder::class);
    }
}
