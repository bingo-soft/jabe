<?php

namespace Jabe\Impl\Form;

use Jabe\Delegate\ExpressionInterface;

class FormDefinition
{
    protected $formKey;
    // extension form definition
    protected $formDefinitionKey;
    protected $formDefinitionBinding;
    protected $formDefinitionVersion;

    public function getFormKey(): ?ExpressionInterface
    {
        return $this->formKey;
    }

    public function setFormKey(ExpressionInterface $formKey): void
    {
        $this->formKey = $formKey;
    }

    public function getFormDefinitionKey(): ExpressionInterface
    {
        return $this->formDefinitionKey;
    }

    public function setFormDefinitionKey(ExpressionInterface $formDefinitionKey): void
    {
        $this->formDefinitionKey = $formDefinitionKey;
    }

    public function getFormDefinitionBinding(): ?string
    {
        return $this->formDefinitionBinding;
    }

    public function setFormDefinitionBinding(?string $formDefinitionBinding): void
    {
        $this->formDefinitionBinding = $formDefinitionBinding;
    }

    public function getFormDefinitionVersion(): ExpressionInterface
    {
        return $this->formDefinitionVersion;
    }

    public function setFormDefinitionVersion(ExpressionInterface $formDefinitionVersion): void
    {
        $this->formDefinitionVersion = $formDefinitionVersion;
    }
}
