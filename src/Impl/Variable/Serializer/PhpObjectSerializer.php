<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\SerializationDataFormats;

class PhpObjectSerializer extends AbstractObjectValueSerializer
{
    public const NAME = "serializable";

    public function __construct()
    {
        parent::__construct(SerializationDataFormats::PHP);
    }

    public function getName(): ?string
    {
        return self::NAME;
    }

    protected function isSerializationTextBased(): bool
    {
        return false;
    }

    protected function deserializeFromByteArray(?string $serialized, /*string*/$objectTypeName)
    {
        if (!empty($serialized)) {
            $deserialized = $serialized;
            preg_match_all("/(C:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $deserialized, $matches);
            if (!empty($matches[2])) {
                foreach ($matches[2] as $className) {
                    $deserialized = str_replace($className, str_replace('.', '\\', $className), $deserialized);
                }
            }
            $deserialized = str_replace('.."', '\\"', $deserialized);
            $deserialized = unserialize($deserialized);
            return $deserialized;
        }
        return null;
    }

    protected function serializeToByteArray($deserializedObject): ?string
    {
        $serialized = serialize($deserializedObject);
        preg_match_all("/(C:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $serialized, $matches);
        if (!empty($matches[2])) {
            foreach ($matches[2] as $className) {
                $serialized = str_replace($className, str_replace('\\', '.', $className), $serialized);
            }
        }
        $serialized = str_replace('\\"', '.."', $serialized);
        return $serialized;
    }

    protected function getTypeNameForDeserialized($deserializedObject): ?string
    {
        return is_object($deserializedObject) ? get_class($deserializedObject) : gettype($deserializedObject);
    }

    protected function canSerializeValue($value): bool
    {
        if (is_array($value) && !empty($value) && (is_string($value[0]) || is_numeric($value[0]) || $value[0] instanceof \Serializable)) {
            return true;
        }
        return $value instanceof \Serializable;
    }
}
