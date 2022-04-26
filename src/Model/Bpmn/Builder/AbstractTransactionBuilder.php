<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\TransactionInterface;

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
