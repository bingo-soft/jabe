<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ManualTaskInterface;

abstract class AbstractManualTaskBuilder extends AbstractTaskBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ManualTaskInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }
}
