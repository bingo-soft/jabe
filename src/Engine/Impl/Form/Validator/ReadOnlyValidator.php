<?php

namespace Jabe\Engine\Impl\Form\Validator;

class ReadOnlyValidator implements FormFieldValidatorInterface
{
    public function validate($submittedValue, FormFieldValidatorContextInterface $validatorContext): bool
    {
        // no value was submitted
        return $submittedValue === null;
    }
}
