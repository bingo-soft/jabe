<?php

namespace BpmPlatform\Engine\Impl\Variable\Serializer;

use BpmPlatform\Engine\Variable\Type\PrimitiveValueTypeInterface;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

abstract class PrimitiveValueSerializer extends AbstractTypedValueSerializer
{
    public function __construct(PrimitiveValueTypeInterface $variableType)
    {
        parent::__construct($variableType);
    }

    public function getName(): string
    {
        // default implementation returns the name of the type. This is OK since we assume that
        // there is only a single serializer for a primitive variable type.
        // If multiple serializers exist for the same type, they must override
        // this method and return distinct values.
        return $this->valueType->getName();
    }

    public function getType(): PrimitiveValueTypeInterface
    {
        return parent::getType();
    }

    protected function canWriteValue(?TypedValueInterface $typedValue): bool
    {
        $value = $typedValue->getValue();
        $phpType = $this->getType()->getPhpType();
        return $value == null || $phpType == gettype($value);
    }
}
