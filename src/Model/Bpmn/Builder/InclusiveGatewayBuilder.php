<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\InclusiveGatewayInterface;

class InclusiveGatewayBuilder extends AbstractInclusiveGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        InclusiveGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, InclusiveGatewayBuilder::class);
    }
}
