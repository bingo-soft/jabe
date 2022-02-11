<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Delegate\{
    DelegateExecutionInterface,
    VariableScopeInterface
};
use BpmPlatform\Engine\Impl\Form\Validator\FormFieldValidatorContextInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\TaskEntity;
use BpmPlatform\Engine\Variable\VariableMapInterface;

class DefaultFormFieldValidatorContext implements FormFieldValidatorContextInterface
{
    protected $variableScope;
    protected $configuration;
    protected $submittedValues;
    protected $formFieldHandler;

    public function __construct(
        VariableScopeInterface $variableScope,
        string $configuration,
        VariableMapInterface $submittedValues,
        FormFieldHandler $formFieldHandler
    ) {
        $this->variableScope = $variableScope;
        $this->configuration = $configuration;
        $this->submittedValues = $submittedValues;
        $this->formFieldHandler = $formFieldHandler;
    }

    public function getFormFieldHandler(): FormFieldHandler
    {
        return $this->formFieldHandler;
    }

    public function getExecution(): DelegateExecutionInterface
    {
        if ($this->variableScope instanceof DelegateExecutionInterface) {
            return $this->variableScope;
        } elseif ($this->variableScope instanceof TaskEntity) {
            return $this->variableScope->getExecution();
        } else {
            return null;
        }
    }

    public function getVariableScope(): VariableScopeInterface
    {
        return $this->variableScope;
    }

    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getSubmittedValues(): array
    {
        return $this->submittedValues;
    }
}
