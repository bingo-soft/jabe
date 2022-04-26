<?php

namespace Jabe\Engine\Impl\Form\Type;

use Jabe\Engine\Form\FormTypeInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

abstract class AbstractFormFieldType implements FormTypeInterface
{
    abstract public function getName(): string;

    abstract public function convertToFormValue(TypedValueInterface $propertyValue): TypedValueInterface;

    abstract public function convertToModelValue(TypedValueInterface $propertyValue): TypedValueInterface;

    public function getInformation(string $key)
    {
        return null;
    }
}
