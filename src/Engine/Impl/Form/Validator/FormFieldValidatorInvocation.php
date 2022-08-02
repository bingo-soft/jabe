<?php

namespace Jabe\Engine\Impl\Form\Validator;

use Jabe\Engine\Impl\Delegate\DelegateInvocation;

class FormFieldValidatorInvocation extends DelegateInvocation
{
    protected $formFieldValidator;
    protected $submittedValue;
    protected $validatorContext;

    public function __construct(FormFieldValidatorInterface $formFieldValidator, $submittedValue, FormFieldValidatorContextInterface $validatorContext)
    {
        parent::__construct(null, null);
        $this->formFieldValidator = $formFieldValidator;
        $this->submittedValue = $submittedValue;
        $this->validatorContext = $validatorContext;
    }

    protected function invoke(): void
    {
        $this->invocationResult = $this->formFieldValidator->validate($this->submittedValue, $this->validatorContext);
    }

    public function getInvocationResult(): bool
    {
        return parent::getInvocationResult();
    }
}
