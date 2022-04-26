<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Type\SerializableValueTypeInterface;
use Jabe\Engine\Variable\Value\{
    ObjectValueInterface,
    SerializableValueInterface,
    TypedValueInterface
};
use Jabe\Engine\Variable\Value\Builder\{
    ObjectValueBuilderInterface,
    SerializedObjectValueBuilderInterface
};

class ObjectTypeImpl extends AbstractValueTypeImpl implements SerializableValueTypeInterface
{
    public const TYPE_NAME = "stdClass";

    public function __construct()
    {
        parent::__construct(self::TYPE_NAME);
    }

    public function isPrimitiveValueType(): bool
    {
        return false;
    }

    public function createValue($value, ?array $valueInfo = null): TypedValueInterface
    {
        $builder = Variables::objectValue($value);

        if (!empty($valueInfo)) {
            $this->applyValueInfo($builder, $valueInfo);
        }

        return $builder->create();
    }

    public function getValueInfo(TypedValueInterface $typedValue): array
    {
        if (!($typedValue instanceof ObjectValueInterface)) {
            throw new \InvalidArgumentException("Value not of type Object.");
        }
        $objectValue = $typedValue;

        $valueInfo = [];

        $serializationDataFormat = $objectValue->getSerializationDataFormat();
        if ($serializationDataFormat != null) {
            $valueInfo[self::VALUE_INFO_SERIALIZATION_DATA_FORMAT] = $serializationDataFormat;
        }

        $objectTypeName = $objectValue->getObjectTypeName();
        if ($objectTypeName != null) {
            $valueInfo[self::VALUE_INFO_OBJECT_TYPE_NAME] = $objectTypeName;
        }

        if ($objectValue->isTransient()) {
            $valueInfo[self::VALUE_INFO_TRANSIENT] = $objectValue->isTransient();
        }

        return $valueInfo;
    }

    public function createValueFromSerialized(string $serializedValue, ?array $valueInfo = null): SerializableValueInterface
    {
        $builder = Variables::serializedObjectValue($serializedValue);

        if ($valueInfo != null) {
            $this->applyValueInfo($builder, $valueInfo);
        }

        return $builder->create();
    }

    protected function applyValueInfo(ObjectValueBuilderInterface $builder, array $valueInfo): void
    {
        $objectValueTypeName = $valueInfo[self::VALUE_INFO_OBJECT_TYPE_NAME];
        if ($builder instanceof SerializedObjectValueBuilder) {
            $builder->objectTypeName($objectValueTypeName);
        }

        $serializationDataFormat = $valueInfo[self::VALUE_INFO_SERIALIZATION_DATA_FORMAT];
        if ($serializationDataFormat != null) {
            $builder->serializationDataFormat($serializationDataFormat);
        }

        $builder->setTransient($this->isTransient($valueInfo));
    }
}
