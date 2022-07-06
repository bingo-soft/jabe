<?php

namespace Jabe\Engine\Impl\Form\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\{
    StringValueInterface,
    TypedValueInterface
};

class StringFormType extends SimpleFormFieldType
{
    public const TYPE_NAME = "string";

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertValue(TypedValueInterface $propertyValue): TypedValueInterface
    {
        if ($propertyValue instanceof StringValueInterface) {
            return $propertyValue;
        } else {
            $value = $propertyValue->getValue();
            if ($value === null) {
                return Variables::stringValue(null, $propertyValue->isTransient());
            } else {
                return Variables::stringValue(strval($value), $propertyValue->isTransient());
            }
        }
    }
}
