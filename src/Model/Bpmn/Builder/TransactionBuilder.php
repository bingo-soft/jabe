<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
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
