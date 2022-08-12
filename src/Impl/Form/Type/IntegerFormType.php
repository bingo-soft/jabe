<?php

namespace Jabe\Impl\Form\Type;

use Jabe\ProcessEngineException;
use Jabe\Variable\Variables;
use Jabe\Variable\Value\{
    IntegerValueInterface,
    TypedValueInterface
};

class IntegerFormType extends SimpleFormFieldType
{
    public const TYPE_NAME = "int";

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertValue(TypedValueInterface $propertyValue): TypedValueInterface
    {
        if ($propertyValue instanceof IntegerValueInterface) {
            return $propertyValue;
        } else {
            $value = $propertyValue->getValue();
            if ($value === null) {
                return Variables::intgerValue(null, $propertyValue->isTransient());
            } elseif (is_numeric($value) || is_string($value)) {
                return Variables::intgerValue(intval($value), $propertyValue->isTransient());
            } else {
                throw new ProcessEngineException("Value '" . $value . "' is not of type int.");
            }
        }
    }
}
