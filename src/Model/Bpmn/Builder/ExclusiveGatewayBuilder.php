<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ExclusiveGatewayInterface;

class ExclusiveGatewayBuilder extends AbstractExclusiveGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ExclusiveGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, ExclusiveGatewayBuilder::class);
    }
}
