<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\IntermediateCatchEventInterface;

class IntermediateCatchEventBuilder extends AbstractIntermediateCatchEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        IntermediateCatchEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, IntermediateCatchEventBuilder::class);
    }
}
