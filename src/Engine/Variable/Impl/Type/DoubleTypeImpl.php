<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\DoubleValueInterface;
use Jabe\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueType
};
use Jabe\Engine\Variable\Value\TypedValueInterface;

class DoubleTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "float");
    }

    public function createValue($value, ?array $valueInfo = null): DoubleValueInterface
    {
        return Variables::dateValue(floatval($value), $this->isTransient($valueInfo));
    }

    public function canConvertFromTypedValue(?TypedValueInterface $typedValue): bool
    {
        if ($typedValue->getType() != ValueType::getDouble()) {
            return false;
        }

        return true;
    }

    public function convertFromTypedValue(TypedValueInterface $typedValue): DoubleValueInterface
    {
        if ($typedValue->getType() != ValueType::getDouble()) {
            throw new \Exception("unsupported conversion");
        }
        $doubleValue = null;
        $numberValue = $typedValue;
        if ($numberValue->getValue() !== null) {
            $doubleValue = Variables::doubleValue($numberValue->getValue()->doubleValue());
        } else {
            $doubleValue = Variables::doubleValue(null);
        }
        $doubleValue->setTransient($numberValue->isTransient());
        return $doubleValue;
    }
}
