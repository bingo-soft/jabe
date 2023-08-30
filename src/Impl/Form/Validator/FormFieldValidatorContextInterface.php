<?php

namespace Jabe\Impl\Form\Validator;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Impl\Form\Handler\FormFieldHandler;

interface FormFieldValidatorContextInterface
{
    public function getFormFieldHandler(): FormFieldHandler;

    /** @return DelegateExecutionInterface the execution
     * Deprecated, use {@link #getVariableScope()} */
    public function getExecution(): DelegateExecutionInterface;

    /**
     * @return VariableScopeInterface the variable scope in which the value is submitted
     */
    public function getVariableScope(): VariableScopeInterface;

    /** @return string the configuration of this validator */
    public function getConfiguration(): ?string;

    /** @return all values submitted in the form */
    public function getSubmittedValues(): array;
}
