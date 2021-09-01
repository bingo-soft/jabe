<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\TransactionInterface;

abstract class AbstractTransactionBuilder extends AbstractSubProcessBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        TransactionInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function method(string $method): AbstractTransactionBuilder
    {
        $this->element->setMethod($method);
        return $this;
    }
}
