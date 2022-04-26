<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\Extension\FormFieldInterface;
use Jabe\Model\Bpmn\Instance\BaseElementInterface;

class UserTaskFormFieldBuilder extends AbstractFormFieldBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BaseElementInterface $parent,
        FormFieldInterface $element
    ) {
        parent::__construct($modelInstance, $parent, $element, UserTaskFormFieldBuilder::class);
    }
}
