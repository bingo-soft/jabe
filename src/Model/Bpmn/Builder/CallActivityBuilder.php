<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\CallActivityInterface;

class CallActivityBuilder extends AbstractCallActivityBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        CallActivityInterface $element
    ) {
        parent::__construct($modelInstance, $element, CallActivityBuilder::class);
    }
}
