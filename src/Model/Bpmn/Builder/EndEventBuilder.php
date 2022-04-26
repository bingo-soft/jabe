<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\EndEventInterface;

class EndEventBuilder extends AbstractEndEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EndEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, EndEventBuilder::class);
    }
}
