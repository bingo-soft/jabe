<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Form\FormFieldValidationConstraintInterface;
use Jabe\Engine\Impl\Form\FormFieldValidationConstraintImpl;
use Jabe\Engine\Impl\Form\Validator\{
    FormFieldValidationException,
    FormFieldValidatorInterface,
    FormFieldValidatorContextInterface,
    FormFieldValidatorInvocation
};
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Variable\VariableMapInterface;

class FormFieldValidationConstraintHandler
{
    protected $name;
    protected $config;
    protected $validator;

    public function createValidationConstraint(ExecutionEntity $execution): FormFieldValidationConstraintInterface
    {
        return new FormFieldValidationConstraintImpl($this->name, $this->config);
    }

    // submit /////////////////////////////////

    public function validate($submittedValue, VariableMapInterface $submittedValues, FormFieldHandler $formFieldHandler, VariableScopeInterface $variableScope): void
    {
        try {
            $context = new DefaultFormFieldValidatorContext($variableScope, $this->config, $submittedValues, $formFieldHandler);
            if (!$this->validator->validate($submittedValue, $context)) {
                throw new \Exception("Invalid value submitted for form field '" . $formFieldHandler->getId() . "'");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // getter / setter ////////////////////////

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setConfig(string $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setValidator(FormFieldValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    public function getValidator(): FormFieldValidator
    {
        return $validator;
    }

    public function __toString()
    {
        return $this->name . ($this->config !== null ? ("(" . $this->config . ")") : "");
    }
}
