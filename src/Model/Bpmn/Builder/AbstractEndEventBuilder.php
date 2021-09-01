<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\EndEventInterface;

abstract class AbstractEndEventBuilder extends AbstractThrowEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EndEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function error(string $errorCode, ?string $errorMessage): AbstractEndEventBuilder
    {
        $errorEventDefinition = $this->createErrorEventDefinition($errorCode, $errorMessage);
        $this->element->addEventDefinition($errorEventDefinition);
        return $this;
    }
}
