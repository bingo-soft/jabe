<?php

namespace BpmPlatform\Engine\Impl\Variable\Serializer;

use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Impl\Value\{
    NullValueImpl,
    UntypedValueImpl
};
use BpmPlatform\Engine\Variable\Type\ValueTypeTrait;
use BpmPlatform\Engine\Variable\Value\{
    NullValueInterface,
    TypedValueInterface
};

class NullValueSerializer extends PrimitiveValueSerializer
{
    public function __construct()
    {
        parent::__construct(ValueTypeTrait::getNull());
    }

    public function getName(): string
    {
        return strtolower(ValueTypeTrait::getNull()->getName());
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
        return $value->getValue() == null;
    }
}
