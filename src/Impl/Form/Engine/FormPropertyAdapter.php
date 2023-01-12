<?php

namespace Jabe\Impl\Form\Engine;

use Jabe\Form\{
    FormFieldInterface,
    FormFieldValidationConstraintInterface,
    FormPropertyInterface,
    FormTypeInterface
};
use Jabe\Impl\Form\FormFieldValidationConstraintImpl;
use Jabe\Variable\Variables;
use Jabe\Variable\Value\TypedValueInterface;

class FormPropertyAdapter implements FormFieldInterface
{
    protected $formProperty;
    protected $validationConstraints;

    public function __construct(FormPropertyInterface $formProperty)
    {
        $this->formProperty = $formProperty;

        $this->validationConstraints = [];
        if ($this->formProperty->isRequired()) {
            $this->validationConstraints[] = new FormFieldValidationConstraintImpl("required", null);
        }
        if (!$this->formProperty->isWritable()) {
            $this->validationConstraints[] = new FormFieldValidationConstraintImpl("readonly", null);
        }
    }

    public function getId(): ?string
    {
        return $this->formProperty->getId();
    }

    public function getLabel(): ?string
    {
        return $this->formProperty->getName();
    }

    public function getType(): FormTypeInterface
    {
        return $this->formProperty->getType();
    }

    public function getTypeName(): ?string
    {
        return $this->formProperty->getType()->getName();
    }

    public function getDefaultValue()
    {
        return $this->formProperty->getValue();
    }

    public function getValidationConstraints(): array
    {
        return $this->validationConstraints;
    }

    public function getProperties(): array
    {
        return [];
    }

    public function isBusinessKey(): bool
    {
        return false;
    }

    public function getDefaultValueTyped(): TypedValueInterface
    {
        return $this->getValue();
    }

    public function getValue(): TypedValueInterface
    {
        return Variables::stringValue($this->formProperty->getValue());
    }
}
