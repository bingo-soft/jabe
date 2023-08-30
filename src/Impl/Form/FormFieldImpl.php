<?php

namespace Jabe\Impl\Form;

use Jabe\Form\{
    FormFieldInterface,
    FormFieldValidationConstraintInterface,
    FormTypeInterface
};
use Jabe\Variable\Value\TypedValueInterface;

class FormFieldImpl implements FormFieldInterface
{
    protected $businessKey;
    protected $id;
    protected $label;
    protected $type;
    protected $defaultValue;
    protected $value;
    protected $validationConstraints = [];
    protected $properties = [];

    public function __serialize(): array
    {
        $validationConstraints = [];
        foreach ($this->validationConstraints as $constraint) {
            $validationConstraints[] = serialize($constraint);
        }
        return [
            'businessKey' => $this->businessKey,
            'id' => $this->id,
            'label' => $this->label,
            'defaultValue' => serialize($this->defaultValue),
            'value' => serialize($this->value),
            'type' => serialize($this->type),
            'properties' => $this->properties
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->businessKey = $data['businessKey'];
        $this->id = $data['id'];
        $this->label = $data['label'];
        $this->defaultValue = unserialize($data['defaultValue']);
        $this->value = unserialize($data['value']);
        $this->type = unserialize($data['type']);
        $this->properties = $data['properties'];
    }

    // getters / setters ///////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getType(): FormTypeInterface
    {
        return $this->type;
    }

    public function getTypeName(): ?string
    {
        return $this->type->getName();
    }

    public function setType(FormTypeInterface $type): void
    {
        $this->type = $type;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getValue(): TypedValueInterface
    {
        return $this->value;
    }

    public function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function setValue(TypedValueInterface $value): void
    {
        $this->value = $value;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function getValidationConstraints(): array
    {
        return $this->validationConstraints;
    }

    public function addValidationConstraint(FormFieldValidationConstraintInterface $constraint): void
    {
        $this->validationConstraints[] = $constraint;
    }

    public function setValidationConstraints(array $validationConstraints): void
    {
        $this->validationConstraints = $validationConstraints;
    }

    public function isBusinessKey(): bool
    {
        return $this->businessKey;
    }

    public function setBusinessKey(bool $businessKey): void
    {
        $this->businessKey = $businessKey;
    }
}
