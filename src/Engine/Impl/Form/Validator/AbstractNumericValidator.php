<?php

namespace Jabe\Engine\Impl\Form\Validator;

abstract class AbstractNumericValidator implements FormFieldValidatorInterface
{
    public function validate($submittedValue, FormFieldValidatorContextInterface $validatorContext): bool
    {
        if ($submittedValue == null) {
            return $this->isNullValid();
        }

        $configurationString = $validatorContext->getConfiguration();

        if ($this->isFLoat($submittedValue)) {
            return $this->validateFloat(floatval($submittedValue), $configuration);
        }

        if ($this->isInteger($submittedValue)) {
            return $this->validateInteger(intval($submittedValue), $configuration);
        }

        throw new FormFieldValidationException("Numeric validator " .  get_class($this) . " cannot be used on non-numeric value ");
    }

    private function isFloat($value): bool
    {
        return is_numeric($value) && stripos($value, '.') !== false;
    }

    private function isInteger($value): bool
    {
        return is_numeric($value) && !$this->isFloat($value);
    }

    protected function isNullValid(): bool
    {
        return true;
    }

    abstract protected function validateFloat(float $submittedValue, $configuration): bool;

    abstract protected function validateInteger(int $submittedValue, $configuration): bool;
}
