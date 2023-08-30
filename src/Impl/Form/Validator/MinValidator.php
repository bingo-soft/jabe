<?php

namespace Jabe\Impl\Form\Validator;

class MinValidator extends AbstractNumericValidator
{
    protected function validateInteger(int $submittedValue, int $configuration): bool
    {
        return $submittedValue >= $configuration;
    }

    protected function validateFloat(float $submittedValue, float $configuration): bool
    {
        return $submittedValue >= $configuration;
    }
}
