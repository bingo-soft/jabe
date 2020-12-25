<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ConditionalEventDefinitionInterface;

class ConditionalEventDefinitionBuilder extends AbstractConditionalEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ConditionalEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, ConditionalEventDefinitionBuilder::class);
    }
}
