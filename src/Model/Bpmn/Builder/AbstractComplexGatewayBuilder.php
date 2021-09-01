<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ComplexGatewayInterface,
    SequenceFlowInterface
};

abstract class AbstractComplexGatewayBuilder extends AbstractGatewayBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ComplexGatewayInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function defaultFlow(SequenceFlowInterface $sequenceFlow): AbstractComplexGatewayBuilder
    {
        $this->element->setDefault($sequenceFlow);
        return $this;
    }

    public function activationCondition(string $conditionExpression): AbstractComplexGatewayBuilder
    {
        $activationCondition = $this->createInstance(ActivationConditionInterface::class);
        $activationCondition->setTextContent($conditionExpression);
        $this->element->setActivationCondition($activationCondition);
        return $this;
    }
}
