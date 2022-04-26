<?php

namespace Jabe\Engine\Form;

interface FormTypeInterface
{
    /** Name for the form type. */
    public function getName(): string;

    /** Retrieve type specific extra information like
     * the list of values for enum types or the format
     * for date types. Look in the userguide for
     * which extra information keys each type provides
     * and what return type they give. */
    public function getInformation(string $key);
}
