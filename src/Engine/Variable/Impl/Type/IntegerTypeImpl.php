<?php

namespace BpmPlatform\Engine\Variable\Impl\Type;

use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Value\IntegerValueInterface;
use BpmPlatform\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueTypeTrait
};
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class IntegerTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "int");
    }

    public function createValue($value, ?array $valueInfo = null): IntegerValueInterface
    {
        return Variables::dateValue(floatval($value), $this->isTransient($valueInfo));
    }

    public function getParent(): ValueTypeInterface
    {
        return ValueTypeTrait::getNumber();
    }

    public function canConvertFromTypedValue(?TypedValueInterface $typedValue): bool
    {
        if ($typedValue->getType() != ValueTypeTrait::getNumber()) {
            return false;
        }

        if ($typedValue->getType() != null) {
            $numberValue = $typedValue;
            $doubleValue = $numberValue->getValue()->doubleValue();

            // returns false if the value changes due to conversion (e.g. by overflows
            // or by loss in precision)
            if ($numberValue->getValue()->intValue() != $doubleValue) {
                return false;
            }
        }

        return true;
    }

    public function convertFromTypedValue(TypedValueInterface $typedValue): IntegerValueInterface
    {
        if ($typedValue->getType() != ValueTypeTrait::getNumber()) {
            throw new \Exception("unsupported conversion");
        }
        $integerValue = null;
        $numberValue = $typedValue;
        if ($numberValue->getValue() != null) {
            $integerValue = Variables::integerValue($numberValue->getValue()->integerValue());
        } else {
            $integerValue = Variables::integerValue(null);
        }
        $integerValue->setTransient($numberValue->isTransient());
        return $integerValue;
    }
}
