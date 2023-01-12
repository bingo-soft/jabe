<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Variable\Type\ValueTypeInterface;
use Jabe\Variable\Value\{
    SerializableValueInterface,
    TypedValueInterface
};

interface TypedValueSerializerInterface
{
    /**
     * The name of this serializer. The name is used when persisting the ValueFields populated by this serializer.
     *
     * @return string the name of this serializer.
     */
    public function getName(): ?string;

    /**
     * The {@link ValueType VariableType} supported
     * @return ValueTypeInterface the VariableType supported
     */
    public function getType(): ValueTypeInterface;

    /**
     * Serialize a TypedValue to the ValueFields.
     *
     * @param value the TypedValue to persist
     * @param valueFields the ValueFields to which the value should be persisted
     */
    public function writeValue($value, ValueFieldsInterface $valueFields): void;

    /**
     * Retrieve a TypedValue from the provided ValueFields.
     *
     * @param valueFields the ValueFields to retrieve the value from
     * @param deserializeValue indicates whether a SerializableValue should be deserialized.
     *
     * @return TypedValueInterface the TypedValue
     */
    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): TypedValueInterface;

    /**
     * Used for auto-detecting the value type of a variable.
     * An implementation must return true if it is able to write values of the provided type.
     *
     * @param value the value
     * @return bool - true if this TypedValueSerializer is able to handle the provided value
     */
    public function canHandle(TypedValueInterface $value): bool;

    /**
     * Returns a typed value for the provided untyped value. This is used on cases where the user sets an untyped
     * value which is then detected to be handled by this TypedValueSerializer (by invocation of {@link #canHandle(TypedValue)}).
     *
     * @param untypedValue the untyped value
     * @return TypedValueInterface the corresponding typed value
     */
    public function convertToTypedValue(UntypedValueImpl $untypedValue): TypedValueInterface;

    /**
     *
     * @return string the dataformat used by the serializer or null if this is not an object serializer
     */
    public function getSerializationDataformat(): ?string;

    /**
     * @return whether values serialized by this serializer can be mutable and
     * should be re-serialized if changed
     */
    public function isMutableValue(TypedValueInterface $typedValue): bool;
}
