<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    TaskInterface
};

abstract class AbstractTaskBuilder extends AbstractActivityBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        TaskInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }
}
