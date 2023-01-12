<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\Impl\Value\{
    NullValueImpl,
    UntypedValueImpl
};
use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\TypedValueInterface;

class NullValueSerializer extends PrimitiveValueSerializer
{
    public function __construct()
    {
        parent::__construct(ValueType::getNull());
    }

    public function getName(): ?string
    {
        return strtolower(ValueType::getNull()->getName());
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): NullValueImpl
    {
        return NullValueImpl::getInstance($untypedValue->isTransient());
    }

    public function writeValue($value, ValueFieldsInterface $valueFields): void
    {
        //
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): NullValueImpl
    {
        return NullValueImpl::getInstance(false);
    }

    protected function canWriteValue(?TypedValueInterface $value): bool
    {
        return $value->getValue() === null;
    }
}
