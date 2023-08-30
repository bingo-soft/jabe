<?php

namespace Jabe\Impl\Form\Validator;

class MinLengthValidator extends AbstractTextValueValidator
{
    protected function validateString(?string $submittedValue, ?string $configuration): bool
    {
        $maxLength = null;
        try {
            $maxLength = intval($configuration);
        } catch (\Exception $e) {
            // do not throw validation exception, as the issue is not with the submitted value
            throw new FormFieldConfigurationException("Cannot validate \"minlength\": configuration");
        }
        return strlen($submittedValue) >= $maxLength;
    }
}
