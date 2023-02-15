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

    protected function deserializeFromByteArray(?string $bytes, /*string*/$objectTypeName)
    {
        /*if (!empty($bytes)) {
            $bytes = str_replace('.', '\\', $bytes);
            if (strpos($bytes, '[') === 0) {
                $bytes = json_decode($bytes, true);
                $res = [];
                foreach ($bytes as $item) {
                    $res[] = unserialize($item);
                }
                return $res;
            }
            return unserialize($bytes);
        }*/
        return null;
    }

    protected function serializeToByteArray($deserializedObject): ?string
    {
        /*if (is_array($deserializedObject)) {
            $res = [];
            foreach ($deserializedObject as $item) {
                $res[] = str_replace('\\', '.', serialize($item));
            }
            return json_encode($res);
        }*/
        $serialized = str_replace('\\', '.', serialize($deserializedObject));
        return $serialized;
    }

    protected function getTypeNameForDeserialized($deserializedObject): ?string
    {
        /*if (is_array($deserializedObject)) {
            return sprintf("Array[%s]", is_object($deserializedObject[0]) ? get_class($deserializedObject[0]) : gettype($deserializedObject[0]));
        }*/
        return is_object($deserializedObject) ? get_class($deserializedObject) : gettype($deserializedObject);
    }

    protected function canSerializeValue($value): bool
    {
        if (is_array($value) && !empty($value) && $value[0] instanceof \Serializable) {
            return true;
        }
        return $value instanceof \Serializable;
    }
}
