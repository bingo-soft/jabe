<?php

namespace Jabe\Engine\Impl\Form\Validator;

class MaxLengthValidator extends AbstractTextValueValidator
{
    protected function validateString(string $submittedValue, string $configuration): bool
    {
        $maxLength = null;
        try {
            $maxLength = intval($configuration);
        } catch (\Exception $e) {
            // do not throw validation exception, as the issue is not with the submitted value
            throw new FormFieldConfigurationException("Cannot validate \"maxlength\": configuration");
        }
        return strlen($submittedValue) <= $maxLength;
    }
}
