<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\IntermediateThrowEventInterface;

abstract class AbstractIntermediateThrowEventBuilder extends AbstractThrowEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        IntermediateThrowEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }
}
