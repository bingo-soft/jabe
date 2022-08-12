<?php

namespace Jabe\Impl\Form\Validator;

abstract class AbstractTextValueValidator implements FormFieldValidatorInterface
{
    public function validate($submittedValue, FormFieldValidatorContextInterface $validatorContext): bool
    {
        if ($submittedValue === null) {
            return $this->isNullValid();
        }

        $configuration = $validatorContext->getConfiguration();

        if (is_string($submittedValue)) {
            return $this->validateString($submittedValue, $configuration);
        }

        throw new FormFieldValidationException("String validator " .  get_class($this) . " cannot be used on non-string value ");
    }

    abstract protected function validateString(string $submittedValue, string $configuration): bool;

    protected function isNullValid(): bool
    {
        return true;
    }
}
