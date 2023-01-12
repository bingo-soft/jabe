<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\Variables;
use Jabe\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\DoubleValueInterface;

class DoubleValueSerializer extends PrimitiveValueSerializer
{
    public function __construct()
    {
        parent::__construct(ValueType::getDouble());
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): DoubleValueInterface
    {
        return Variables::doubleValue($untypedValue->getValue(), $untypedValue->isTransient());
    }

    public function writeValue($value, ValueFieldsInterface $valueFields): void
    {
        $valueFields->setDoubleValue($value->getValue());
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): DoubleValueInterface
    {
        return Variables::doubleValue($valueFields->getDoubleValue(), $isTransient);
    }
}
