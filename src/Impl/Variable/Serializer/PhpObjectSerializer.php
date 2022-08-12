<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\SerializationDataFormats;

class PhpObjectSerializer extends AbstractObjectValueSerializer
{
    public const NAME = "serializable";

    public function __construct()
    {
        parent::__construct(SerializationDataFormats::JAVA);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    protected function isSerializationTextBased(): bool
    {
        return false;
    }

    protected function deserializeFromByteArray(string $bytes, string $objectTypeName)
    {
        return unserialize($bytes);
    }

    protected function serializeToByteArray($deserializedObject): string
    {
        return serialize($deserializedObject);
    }

    protected function getTypeNameForDeserialized($deserializedObject): string
    {
        return get_class($deserializedObject);
    }

    protected function canSerializeValue($value): bool
    {
        return $value instanceof \Serializable;
    }
}
