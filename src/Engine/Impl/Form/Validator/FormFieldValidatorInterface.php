<?php

namespace BpmPlatform\Engine\Impl\Form\Validator;

interface FormFieldValidatorInterface
{
    /**
     * return true if the submitted value is valid for the given form field.
     *
     * @param submittedValue
     *          the value submitted to the form field
     * @param validatorContext
     *          object providing access to additional information useful wile
     *          validating the form
     * @return true if the value is valid, false otherwise.
     */
    public function validate($submittedValue, FormFieldValidatorContextInterface $validatorContext): bool;
}
