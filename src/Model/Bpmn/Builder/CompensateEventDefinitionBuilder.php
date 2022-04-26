<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\CompensateEventDefinitionInterface;

class CompensateEventDefinitionBuilder extends AbstractCompensateEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        CompensateEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, CompensateEventDefinitionBuilder::class);
    }
}
