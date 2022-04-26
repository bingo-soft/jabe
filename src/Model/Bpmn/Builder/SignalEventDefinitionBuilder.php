<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\SignalEventDefinitionInterface;

class SignalEventDefinitionBuilder extends AbstractSignalEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SignalEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, SignalEventDefinitionBuilder::class);
    }
}
