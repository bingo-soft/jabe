<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ErrorEventDefinitionInterface;

class ErrorEventDefinitionBuilder extends AbstractErrorEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ErrorEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, ErrorEventDefinitionBuilder::class);
    }
}
