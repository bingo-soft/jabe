<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    TransactionInterface
};

class TransactionBuilder extends AbstractTransactionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        TransactionInterface $element
    ) {
        parent::__construct($modelInstance, $element, TransactionBuilder::class);
    }
}
