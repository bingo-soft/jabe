<?php

namespace BpmPlatform\Engine\Impl\Variable\Serializer;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Variable\Impl\Value\UntypedValueImpl;
use BpmPlatform\Engine\Variable\Type\SerializableValueTypeInterface;
use BpmPlatform\Engine\Variable\Value\{
    SerializableValueInterface,
    TypedValueInterface
};
use BpmPlatform\Engine\Impl\Util\StringUtil;

abstract class AbstractSerializableValueSerializer extends AbstractTypedValueSerializer
{
    protected $serializationDataFormat;

    public function __construct(SerializableValueTypeInterface $type, string $serializationDataFormat)
    {
        parebt::__construct($type);
        $this->serializationDataFormat = $serializationDataFormat;
    }

    public function getSerializationDataformat(): string
    {
        return $this->serializationDataFormat;
    }

    public function writeValue(SerializableValueInterface $value, ValueFieldsInterface $valueFields): void
    {
        $serializedStringValue = $value->getValueSerialized();
        $serializedByteValue = null;

        if ($value->isDeserialized()) {
            $objectToSerialize = $value->getValue();
            if ($objectToSerialize != null) {
                // serialize to byte array
                try {
                    $serializedByteValue = $this->serializeToByteArray($objectToSerialize);
                    $serializedStringValue = $serializedByteValue;
                } catch (\Exception $e) {
                    throw new ProcessEngineException("Cannot serialize object in variable '" . $valueFields->getName());
                }
            }
        } else {
            if ($serializedStringValue != null) {
                $serializedByteValue = $this->serializedStringValue;
            }
        }

        // write value and type to fields.
        $this->writeToValueFields($value, $valueFields, $serializedByteValue);

        // update the ObjectValue to keep it consistent with value fields.
        $this->updateTypedValue($value, $serializedStringValue);
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): SerializableValueInterface
    {
        $serializedByteValue = $this->readSerializedValueFromFields($valueFields);
        $serializedStringValue = $serializedByteValue;

        if ($deserializeObjectValue) {
            $deserializedObject = null;
            if ($serializedByteValue != null) {
                try {
                    $deserializedObject = $this->deserializeFromByteArray($serializedByteValue, $valueFields);
                } catch (\Exception $e) {
                    throw new ProcessEngineException("Cannot deserialize object in variable '" . $valueFields->getName());
                }
            }
            $value = $this->createDeserializedValue($deserializedObject, $serializedStringValue, $valueFields, $asTransientValue);
            return $value;
        } else {
            return $this->createSerializedValue($serializedStringValue, $valueFields, $asTransientValue);
        }
    }

    abstract protected function createDeserializedValue($deserializedObject, string $serializedStringValue, ValueFieldsInterface $valueFields, bool $asTransientValue): SerializableValueInterface;

    abstract protected function createSerializedValue(string $serializedStringValue, ValueFieldsInterface $valueFields, bool $asTransientValue): SerializableValueInterface;

    abstract protected function writeToValueFields(SerializableValueInterface $value, ValueFieldsInterface $valueFields, string $serializedValue): void;

    abstract protected function updateTypedValue(SerializableValueInterface $value, string $serializedStringValue): void;

    protected function readSerializedValueFromFields(ValueFieldsInterface $valueFields): string
    {
        return $valueFields->getByteArrayValue();
    }

    protected function canWriteValue(?TypedValueInterface $typedValue): bool
    {
        if (!($typedValue instanceof SerializableValueInterface) && !($typedValue instanceof UntypedValueImpl)) {
            return false;
        }

        if ($typedValue instanceof SerializableValueInterface) {
            $serializableValue = $typedValue;
            $requestedDataFormat = $serializableValue->getSerializationDataFormat();
            if (!$serializableValue->isDeserialized()) {
                // serialized object => dataformat must match
                return $this->serializationDataFormat == $requestedDataFormat;
            } else {
                $canSerialize = $typedValue->getValue() == null || $this->canSerializeValue($typedValue->getValue());
                return $canSerialize && ($requestedDataFormat == null || $this->serializationDataFormat == $requestedDataFormat);
            }
        } else {
            return $typedValue->getValue() == null || $this->canSerializeValue($typedValue->getValue());
        }
    }

    /**
     * return true if this serializer is able to serialize the provided object.
     *
     * @param value the object to test (guaranteed to be a non-null value)
     * @return bool - true if the serializer can handle the object.
     */
    abstract protected function canSerializeValue($value): bool;

    // methods to be implemented by subclasses ////////////

    /**
     * Implementations must return a byte[] representation of the provided object.
     * The object is guaranteed not to be null.
     *
     * @param deserializedObject the object to serialize
     * @return the byte array value of the object
     * @throws exception in case the object cannot be serialized
     */
    abstract protected function serializeToByteArray($deserializedObject): string;

    /**
     * Deserialize the object from a byte array.
     *
     * @param object the object to deserialize
     * @param valueFields the value fields
     * @return the deserialized object
     * @throws exception in case the object cannot be deserialized
     */
    abstract protected function deserializeFromByteArray(string $object, ValueFieldsInterface $valueFields);

    /**
     * Return true if the serialization is text based. Return false otherwise
     *
     */
    abstract protected function isSerializationTextBased(): bool;
}
