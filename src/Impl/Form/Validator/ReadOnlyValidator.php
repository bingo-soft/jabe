<?php

namespace Jabe\Impl\Form\Validator;

class ReadOnlyValidator implements FormFieldValidatorInterface
{
    public function validate($submittedValue, FormFieldValidatorContextInterface $validatorContext): bool
    {
        // no value was submitted
        return $submittedValue === null;
    }
}
