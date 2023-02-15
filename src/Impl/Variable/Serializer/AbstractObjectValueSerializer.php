<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\ProcessEngineException;
use Jabe\Variable\Variables;
use Jabe\Variable\Impl\Value\{
    ObjectValueImpl,
    UntypedValueImpl
};
use Jabe\Variable\Type\{
    ValueTypeInterface,
    ValueType
};
use Jabe\Variable\Value\{
    ObjectValueInterface,
    TypedValueInterface
};

abstract class AbstractObjectValueSerializer extends AbstractSerializableValueSerializer
{
    public function __construct(?string $serializationDataFormat)
    {
        parent::__construct(ValueType::getObject(), $serializationDataFormat);
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): ObjectValueInterface
    {
        // untyped values are always deserialized
        return Variables::objectValue($untypedValue->getValue(), $untypedValue->isTransient())->create();
    }

    protected function writeToValueFields(/*ObjectValueInterface*/$value, ValueFieldsInterface $valueFields, ?string $serializedValue): void
    {
        $objectTypeName = $this->getObjectTypeName($value, $valueFields);
        $valueFields->setByteArrayValue($serializedValue);
        $valueFields->setTextValue2($objectTypeName);
    }

    protected function getObjectTypeName(ObjectValueInterface $value, ?ValueFieldsInterface $valueFields): ?string
    {
        $objectTypeName = $value->getObjectTypeName();

        if ($objectTypeName === null && !$value->isDeserialized() && $value->getValueSerialized() !== null) {
            throw new ProcessEngineException("Cannot write serialized value for variable '" . $valueFields->getName() . "': no 'objectTypeName' provided for non-null value.");
        }

        // update type name if the object is deserialized
        if ($value->isDeserialized() && $value->getValue() !== null) {
            $objectTypeName = $this->getTypeNameForDeserialized($value->getValue());
        }

        return $objectTypeName;
    }

    protected function updateTypedValue(/*ObjectValueInterface*/$value, ?string $serializedStringValue): void
    {
        $objectTypeName = $this->getObjectTypeName($value, null);
        $objectValue = $value;
        $objectValue->setObjectTypeName($objectTypeName);
        $objectValue->setSerializedValue($serializedStringValue);
        $objectValue->setSerializationDataFormat($this->serializationDataFormat);
    }

    protected function createDeserializedValue(
        $deserializedObject,
        ?string $serializedStringValue,
        ValueFieldsInterface $valueFields,
        bool $asTransientValue
    ): ObjectValueInterface {
        $objectTypeName = $this->readObjectNameFromFields($valueFields);
        $objectValue = new ObjectValueImpl($deserializedObject, $serializedStringValue, $this->serializationDataFormat, $objectTypeName, true);
        $objectValue->setTransient($asTransientValue);
        return $objectValue;
    }


    protected function createSerializedValue(
        ?string $serializedStringValue,
        ValueFieldsInterface $valueFields,
        bool $asTransientValue
    ): ObjectValueInterface {
        $objectTypeName = $this->readObjectNameFromFields($valueFields);
        $objectValue = new ObjectValueImpl(null, $serializedStringValue, $this->serializationDataFormat, $objectTypeName, false);
        $objectValue->setTransient($asTransientValue);
        return $objectValue;
    }

    protected function readObjectNameFromFields(ValueFieldsInterface $valueFields): ?string
    {
        return $valueFields->getTextValue2();
    }

    public function isMutableValue(TypedValueInterface $typedValue): bool
    {
        return $typedValue->isDeserialized();
    }

    // methods to be implemented by subclasses ////////////

    /**
     * Returns the type name for the deserialized object.
     *
     * @param deserializedObject. Guaranteed not to be null
     * @return string the type name fot the object.
     */
    abstract protected function getTypeNameForDeserialized($deserializedObject): ?string;

    /**
     * Implementations must return a byte[] representation of the provided object.
     * The object is guaranteed not to be null.
     *
     * @param deserializedObject the object to serialize
     * @return string the byte array value of the object
     * @throws exception in case the object cannot be serialized
     */
    abstract protected function serializeToByteArray($deserializedObject): ?string;

    /**
     * Deserialize the object from a byte array.
     *
     * @param object the object to deserialize
     * @param objectTypeName the type name of the object to deserialize
     * @return mixed the deserialized object
     * @throws exception in case the object cannot be deserialized
     */
    abstract protected function deserializeFromByteArray(?string $object, /*string*/$objectTypeName);

    /**
     * Return true if the serialization is text based. Return false otherwise
     *
     */
    abstract protected function isSerializationTextBased(): bool;
}
