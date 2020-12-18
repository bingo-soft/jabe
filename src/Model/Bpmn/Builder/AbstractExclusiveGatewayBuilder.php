<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ExclusiveGatewayInterface,
    SequenceFlowInterface
};

abstract class AbstractExclusiveGatewayBuilder extends AbstractGatewayBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ExclusiveGatewayInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function defaultFlow(SequenceFlowInterface $sequenceFlow): AbstractExclusiveGatewayBuilder
    {
        $this->element->setDefault($sequenceFlow);
        return $this->myself;
    }
}
