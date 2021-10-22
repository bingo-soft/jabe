<?php

namespace BpmPlatform\Engine\Variable\Impl\Type;

use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Value\DoubleValueInterface;
use BpmPlatform\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueTypeTrait
};
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class DoubleTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "double");
    }

    public function createValue($value, ?array $valueInfo = null): DoubleValueInterface
    {
        return Variables::dateValue(floatval($value), $this->isTransient($valueInfo));
    }

    public function canConvertFromTypedValue(?TypedValueInterface $typedValue): bool
    {
        if ($typedValue->getType() != ValueTypeTrait::getDouble()) {
            return false;
        }

        return true;
    }

    public function convertFromTypedValue(TypedValueInterface $typedValue): DoubleValueInterface
    {
        if ($typedValue->getType() != ValueTypeTrait::getDouble()) {
            throw new \Exception("unsupported conversion");
        }
        $doubleValue = null;
        $numberValue = $typedValue;
        if ($numberValue->getValue() != null) {
            $doubleValue = Variables::doubleValue($numberValue->getValue()->doubleValue());
        } else {
            $doubleValue = Variables::doubleValue(null);
        }
        $doubleValue->setTransient($numberValue->isTransient());
        return $doubleValue;
    }
}
