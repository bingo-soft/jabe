<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
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
