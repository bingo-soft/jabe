<?php

namespace Jabe\Engine\Variable\Value;

use Jabe\Engine\Variable\Type\SerializableValueTypeInterface;

interface SerializableValueInterface extends TypedValueInterface
{
    /**
     * Returns true in case the value is deserialized. If this method returns true,
     * it is safe to call the {@link #getValue()} method
     *
     * @return bool - true if the object is deserialized.
     */
    public function isDeserialized(): bool;

    /**
     * Returns the value or null in case the value is null.
     *
     * @return the value represented by this TypedValue.
     * @throws IllegalStateException in case the value is not deserialized. See {@link #isDeserialized()}.
     */
    public function getValue(?string $type = null);

    /**
     * Returns the serialized value. In case the serializaton data format
     * (as returned by {@link #getSerializationDataFormat()}) is not text based,
     * a base 64 encoded representation of the value is returned
     *
     * The serialized value is a snapshot of the state of the value as it is
     * serialized to the process engine database.
     */
    public function getValueSerialized(): string;

    /**
     * The serialization format used to serialize this value.
     *
     * @return the serialization format used to serialize this variable.
     */
    public function getSerializationDataFormat(): string;

    public function getType(): SerializableValueTypeInterface;
}
