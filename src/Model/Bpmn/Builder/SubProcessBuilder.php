<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    SubProcessInterface
};

class SubProcessBuilder extends AbstractSubProcessBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SubProcessInterface $element
    ) {
        parent::__construct($modelInstance, $element, SubProcessBuilder::class);
    }
}
