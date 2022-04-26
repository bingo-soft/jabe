<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ReceiveTaskInterface;

class ReceiveTaskBuilder extends AbstractReceiveTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ReceiveTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ReceiveTaskBuilder::class);
    }
}
