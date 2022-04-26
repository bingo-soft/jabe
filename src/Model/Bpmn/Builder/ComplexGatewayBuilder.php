<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ComplexGatewayInterface;

class ComplexGatewayBuilder extends AbstractComplexGatewayBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ComplexGatewayInterface $element
    ) {
        parent::__construct($modelInstance, $element, ComplexGatewayBuilder::class);
    }
}
