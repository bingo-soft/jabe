<?php

namespace Jabe\Engine\Form;

interface FormFieldValidationConstraintInterface
{
    /** @return string the name of the validation constraint */
    public function getName(): string;

    /** @return mixed the configuration of the validation constraint. */
    public function getConfiguration();
}
