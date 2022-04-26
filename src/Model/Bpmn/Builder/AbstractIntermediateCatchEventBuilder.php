<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\IntermediateCatchEventInterface;

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
