<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\StartEventInterface;

class StartEventBuilder extends AbstractStartEventBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        StartEventInterface $element
    ) {
        parent::__construct($modelInstance, $element, StartEventBuilder::class);
    }
}
