<?php

namespace Jabe\Impl\Form\Type;

use Jabe\Form\FormTypeInterface;
use Jabe\Variable\Value\TypedValueInterface;

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
