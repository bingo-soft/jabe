<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\Extension\FormFieldInterface;
use BpmPlatform\Model\Bpmn\Instance\BaseElementInterface;

class StartEventFormFieldBuilder extends AbstractFormFieldBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BaseElementInterface $parent,
        FormFieldInterface $element
    ) {
        parent::__construct($modelInstance, $parent, $element, StartEventFormFieldBuilder::class);
    }
}
