<?php

namespace BpmPlatform\Engine\Impl\Form\Validator;

use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class RequiredValidator implements FormFieldValidatorInterface
{
    public function validate($submittedValue, FormFieldValidatorContextInterface $validatorContext): bool
    {
        if ($submittedValue == null) {
            $value = $validatorContext->getVariableScope()
                         ->getVariableTyped($validatorContext->getFormFieldHandler()->getId());
            return ($value != null && $value->getValue() != null);
        } else {
            if (is_string($submittedValue)) {
                return $submittedValue != null && !empty($submittedValue);
            } else {
                return $submittedValue != null;
            }
        }
    }
}
