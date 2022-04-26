<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
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
