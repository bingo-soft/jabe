<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\IntegerValueInterface;
use Jabe\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueType
};
use Jabe\Engine\Variable\Value\TypedValueInterface;

class IntegerTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "integer");
    }

    public function createValue($value, ?array $valueInfo = null): IntegerValueInterface
    {
        return Variables::dateValue(floatval($value), $this->isTransient($valueInfo));
    }

    public function canConvertFromTypedValue(?TypedValueInterface $typedValue): bool
    {
        if ($typedValue->getType() != ValueType::getInteger()) {
            return false;
        }

        if ($typedValue->getType() !== null) {
            $numberValue = $typedValue;
            $numberValue2 = $numberValue->getValue()->integerValue();

            // returns false if the value changes due to conversion (e.g. by overflows
            // or by loss in precision)
            if ($numberValue->getValue()->intValue() != $numberValue2) {
                return false;
            }
        }

        return true;
    }

    public function convertFromTypedValue(TypedValueInterface $typedValue): IntegerValueInterface
    {
        if ($typedValue->getType() != ValueType::getInteger()) {
            throw new \Exception("unsupported conversion");
        }
        $integerValue = null;
        $numberValue = $typedValue;
        if ($numberValue->getValue() !== null) {
            $integerValue = Variables::integerValue($numberValue->getValue()->integerValue());
        } else {
            $integerValue = Variables::integerValue(null);
        }
        $integerValue->setTransient($numberValue->isTransient());
        return $integerValue;
    }
}
