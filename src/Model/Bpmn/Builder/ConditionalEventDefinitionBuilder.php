<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ConditionalEventDefinitionInterface;

class ConditionalEventDefinitionBuilder extends AbstractConditionalEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ConditionalEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, ConditionalEventDefinitionBuilder::class);
    }
}
