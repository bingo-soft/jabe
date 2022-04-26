<?php

namespace Jabe\Engine\Impl\Form\Type;

use Jabe\Engine\Variable\Value\TypedValueInterface;

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
