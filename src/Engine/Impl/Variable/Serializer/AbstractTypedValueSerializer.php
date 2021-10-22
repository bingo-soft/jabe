<?php

namespace BpmPlatform\Engine\Impl\Variable\Serializer;

use BpmPlatform\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueTypeTrait
};
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

abstract class AbstractTypedValueSerializer implements TypedValueSerializerInterface
{
    public static $BINARY_VALUE_TYPES;

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
        if ($value->getType() != null && !is_a($this->valueType, get_class($value->getType))) {
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
