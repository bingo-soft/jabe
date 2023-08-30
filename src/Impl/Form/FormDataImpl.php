<?php

namespace Jabe\Impl\Form;

use Jabe\Form\{
    FormDataInterface,
    FormFieldInterface,
    FormPropertyInterface
};

class FormDataImpl implements FormDataInterface
{
    protected $formKey;
    protected $deploymentId;
    protected $formProperties = [];
    protected $formFields = [];

    public function __serialize(): array
    {
        $formProperties = [];
        foreach ($this->formProperties as $prop) {
            $formProperties[] = serialize($prop);
        }
        $formFields = [];
        foreach ($this->formFields as $field) {
            $formFields[] = serialize($field);
        }
        return [
            'formKey' => $this->formKey,
            'deploymentId' => $this->deploymentId,
            'formProperties' => $formProperties,
            'formFields' => $formFields
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->formKey = $data['formKey'];
        $this->deploymentId = $data['deploymentId'];

        $props = [];
        foreach ($data['formProperties'] as $prop) {
            $props[] = unserialize($prop);
        }
        $this->formProperties = $props;

        $fields = [];
        foreach ($data['formFields'] as $field) {
            $fields[] = unserialize($field);
        }
        $this->formFields = $fields;
    }

    public function getFormKey(): ?string
    {
        return $this->formKey;
    }
    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }
    public function getFormProperties(): array
    {
        return $this->formProperties;
    }

    public function setFormKey(?string $formKey): void
    {
        $this->formKey = $formKey;
    }

    public function setDeploymentId(?string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function setFormProperties(array $formProperties): void
    {
        $this->formProperties = $formProperties;
    }

    public function getFormFields(): array
    {
        return $this->formFields;
    }

    public function addFormField(FormFieldInterface $field): void
    {
        $this->formFields[] = $field;
    }

    public function setFormFields(array $formFields): void
    {
        $this->formFields = $formFields;
    }
}
