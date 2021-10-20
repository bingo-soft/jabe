<?php

namespace BpmPlatform\Engine\Form;

interface FormFieldValidationConstraintInterface
{
    /** @return the name of the validation constraint */
    public function getName(): string;

    /** @return the configuration of the validation constraint. */
    public function getConfiguration();
}
