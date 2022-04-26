<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
    BpmnModelElementInstanceInterface,
    EndEventInterface,
    IntermediateThrowEventInterface,
    SubProcessInterface,
    TransactionInterface
};

abstract class AbstractBpmnModelElementBuilder
{
    protected $modelInstance;
    protected $element;

    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BpmnModelElementInstanceInterface $element,
        string $selfType
    ) {
        $this->modelInstance = $modelInstance;
        $this->element = $element;
    }

    public function done(): BpmnModelInstanceInterface
    {
        return $this->modelInstance;
    }

    public function subProcessDone(): SubProcessBuilder
    {
        $lastSubProcess = $this->element->getScope();
        if ($lastSubProcess != null && $lastSubProcess instanceof SubProcessInterface) {
            return $lastSubProcess->builder();
        } else {
            throw new BpmnModelException("Unable to find a parent subProcess.");
        }
    }

    public function transactionDone(): TransactionBuilder
    {
        $lastTransaction = $this->element->getScope();
        if ($lastTransaction != null && $lastTransaction instanceof TransactionInterface) {
            return new TransactionBuilder($this->modelInstance, $lastTransaction);
        } else {
            throw new BpmnModelException("Unable to find a parent transaction.");
        }
    }

    public function throwEventDefinitionDone(): AbstractThrowEventBuilder
    {
        $lastEvent = $this->element->getDomElement()->getParentElement()->getModelElementInstance();
        if ($lastEvent != null && $lastEvent instanceof IntermediateThrowEventInterface) {
            return new IntermediateThrowEventBuilder($this->modelInstance, $lastEvent);
        } elseif ($lastEvent != null && $lastEvent instanceof EndEventInterface) {
            return new EndEventBuilder($this->modelInstance, $lastEvent);
        } else {
            throw new BpmnModelException("Unable to find a parent event.");
        }
    }

    public function getElement(): BpmnModelElementInstanceInterface
    {
        return $this->element;
    }
}
