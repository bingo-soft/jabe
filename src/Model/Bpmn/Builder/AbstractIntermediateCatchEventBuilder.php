<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\IntermediateCatchEventInterface;

abstract class AbstractIntermediateCatchEventBuilder extends AbstractCatchEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        IntermediateCatchEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }
}
