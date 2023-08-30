<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\Type\{
    ValueTypeInterface,
    ValueType
};
use Jabe\Variable\Value\TypedValueInterface;

abstract class AbstractTypedValueSerializer implements TypedValueSerializerInterface
{
    public const BINARY_VALUE_TYPES = ["bytes", "file"];

    protected $valueType;

    public function __construct(ValueTypeInterface $type)
    {
        $this->valueType = $type;
    }

    public function getType(): ValueTypeInterface
    {
        return $this->valueType;
    }

    public function getSerializationDataformat(): ?string
    {
        // default implementation returns null
        return null;
    }

    public function canHandle(TypedValueInterface $value): bool
    {
        if ($value->getType() !== null && !is_a($this->valueType, get_class($value->getType()))) {
            return false;
        } else {
            return $this->canWriteValue($value);
        }
    }

    abstract protected function canWriteValue(?TypedValueInterface $value): bool;

    public function isMutableValue(TypedValueInterface $typedValue): bool
    {
        // default
        return false;
    }
}
