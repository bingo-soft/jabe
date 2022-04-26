<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\Extension\FormFieldInterface;
use Jabe\Model\Bpmn\Instance\BaseElementInterface;

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
        $this->element->setLabel($label);
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

    /**
     * @return mixed
     */
    public function formFieldDone()
    {
        return $this->parent->builder();
    }
}
