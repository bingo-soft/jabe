<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\EndEventInterface;

class EndEventBuilder extends AbstractEndEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EndEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, EndEventBuilder::class);
    }
}
