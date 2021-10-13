<?php

namespace BpmPlatform\Engine\Variable\Type;

interface SerializableValueTypeInterface extends ValueTypeInterface
{
    /**
     * Identifies the object's java type name.
     */
    public const VALUE_INFO_OBJECT_TYPE_NAME = "objectTypeName";

    /**
     * Identifies the format in which the object is serialized.
     */
    public const VALUE_INFO_SERIALIZATION_DATA_FORMAT = "serializationDataFormat";

    /**
     * Creates a new TypedValue using this type.
     * @param serializedValue the value in serialized form
     * @return the typed value for the value
     */
    public function createValueFromSerialized(string $serializedValue, array $valueInfo): SerializableValueInterface;
}
