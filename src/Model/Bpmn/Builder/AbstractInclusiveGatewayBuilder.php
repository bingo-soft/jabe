<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    InclusiveGatewayInterface,
    SequenceFlowInterface
};

abstract class AbstractInclusiveGatewayBuilder extends AbstractGatewayBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        InclusiveGatewayInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function defaultFlow(SequenceFlowInterface $sequenceFlow): AbstractInclusiveGatewayBuilder
    {
        $this->element->setDefault($sequenceFlow);
        return $this;
    }
}
