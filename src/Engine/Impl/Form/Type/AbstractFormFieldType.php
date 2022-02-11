<?php

namespace BpmPlatform\Engine\Impl\Form\Type;

use BpmPlatform\Engine\Form\FormTypeInterface;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

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
