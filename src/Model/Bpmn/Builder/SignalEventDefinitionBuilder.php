<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\SignalEventDefinitionInterface;

class SignalEventDefinitionBuilder extends AbstractSignalEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SignalEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, SignalEventDefinitionBuilder::class);
    }
}
