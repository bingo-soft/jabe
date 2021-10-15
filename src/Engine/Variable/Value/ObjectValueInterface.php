<?php

namespace BpmPlatform\Engine\Variable\Value;

interface ObjectValueInterface extends SerializableValueInterface
{
    /**
    * Returns true in case the object is deserialized. If this method returns true,
    * it is safe to call the methods
    * <ul>
    *   <li>{@link #getValue()} and {@link #getValue(Class)}</li>
    *   <li>{@link #getObjectType()}</li>
    * </ul>
    *
    * @return true if the object is deserialized.
    */
    public function isDeserialized(): bool;

    /**
     * Returns the Object or null in case the value is null.
     *
     * @return the object represented by this TypedValue.
     * @throws IllegalStateException in case the object is not deserialized. See {@link #isDeserialized()}.
     */
    public function getValue(?string $type = null);

    /**
     * Returns the Class this object is an instance of.
     *
     * @return the Class this object is an instance of
     * @throws IllegalStateException in case the object is not deserialized. See {@link #isDeserialized()}.
     */
    public function getObjectType(): ?string;

    /**
    * A String representation of the Object's type name.
    * Usually the canonical class name of the Java Class this object
    * is an instance of.
    *
    * @return the Object's type name.
    */
    public function getObjectTypeName(): ?string;
}
