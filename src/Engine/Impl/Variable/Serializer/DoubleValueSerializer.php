<?php

namespace Jabe\Engine\Impl\Variable\Serializer;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\DoubleValueInterface;

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

    public function writeValue(DoubleValueInterface $value, ValueFieldsInterface $valueFields): void
    {
        $valueFields->setDoubleValue($value->getValue());
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): DoubleValueInterface
    {
        return Variables::doubleValue($valueFields->getDoubleValue(), $isTransient);
    }
}
