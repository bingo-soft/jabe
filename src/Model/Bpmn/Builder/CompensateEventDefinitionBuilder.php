<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\CompensateEventDefinitionInterface;

class CompensateEventDefinitionBuilder extends AbstractCompensateEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        CompensateEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, CompensateEventDefinitionBuilder::class);
    }
}
