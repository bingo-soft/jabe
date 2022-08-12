<?php

namespace Jabe\Impl\Form\Type;

use Jabe\Variable\Value\TypedValueInterface;

abstract class SimpleFormFieldType extends AbstractFormFieldType
{
    public function convertToFormValue(TypedValueInterface $propertyValue): TypedValueInterface
    {
        return $this->convertValue($propertyValue);
    }

    public function convertToModelValue(TypedValueInterface $propertyValue): TypedValueInterface
    {
        return $this->convertValue($propertyValue);
    }

    abstract protected function convertValue(TypedValueInterface $propertyValue): TypedValueInterface;
}
