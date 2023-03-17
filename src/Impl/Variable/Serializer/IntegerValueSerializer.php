<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\Variables;
use Jabe\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\IntegerValueInterface;

class IntegerValueSerializer extends PrimitiveValueSerializer
{
    public function __construct()
    {
        parent::__construct(ValueType::getInteger());
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): IntegerValueInterface
    {
        return Variables::integerValue($untypedValue->getValue(), $untypedValue->isTransient());
    }

    public function writeValue($value, ValueFieldsInterface $valueFields): void
    {
        $valueFields->setLongValue($value->getValue());
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $deserializeValue, bool $isTransient = false): IntegerValueInterface
    {
        return Variables::integerValue($valueFields->getLongValue(), $isTransient);
    }
}
