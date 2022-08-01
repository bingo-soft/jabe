<?php

namespace Jabe\Engine\Impl\Variable\Serializer;

use Jabe\Engine\Variable\Impl\Value\{
    NullValueImpl,
    UntypedValueImpl
};
use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\{
    NullValueInterface,
    TypedValueInterface
};

class NullValueSerializer extends PrimitiveValueSerializer
{
    public function __construct()
    {
        parent::__construct(ValueType::getNull());
    }

    public function getName(): string
    {
        return strtolower(ValueType::getNull()->getName());
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): NullValueInterface
    {
        return NullValueImpl::getInstance($untypedValue->isTransient());
    }

    public function writeValue(NullValueInterface $value, ValueFieldsInterface $valueFields): void
    {
        //
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): NullValueInterface
    {
        return NullValueImpl::getInstance(false);
    }

    protected function canWriteValue(?TypedValueInterface $value): bool
    {
        return $value->getValue() === null;
    }
}
