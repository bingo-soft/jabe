<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    FormFieldInterface
};

abstract class AbstractFormFieldBuilder extends AbstractBpmnModelElementBuilder
{
    protected $parent;

    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BaseElementInterface $parent,
        FormFieldInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
        $this->parent = $parent;
    }

    public function id(string $id): AbstractFormFieldBuilder
    {
        $this->element->setId($id);
        return $this;
    }

    public function label(string $label): AbstractFormFieldBuilder
    {
        $this->element->setLabel($id);
        return $this;
    }

    public function type(string $type): AbstractFormFieldBuilder
    {
        $this->element->setType($type);
        return $this;
    }

    public function defaultValue(string $defaultValue): AbstractFormFieldBuilder
    {
        $this->element->setDefaultValue($defaultValue);
        return $this;
    }

    public function camundaFormFieldDone(): AbstractFormFieldBuilder
    {
        return parent::builder();
    }
}
