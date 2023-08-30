<?php

namespace Jabe\Variable\Type;

use Jabe\Variable\Value\TypedValueInterface;

interface ValueTypeInterface
{
    public const VALUE_INFO_TRANSIENT = "transient";

    /**
     * Returns the name of the variable type
     */
    public function getName(): ?string;

    /**
     * Indicates whether this type is primitive valued. Primitive valued types can be handled
     * natively by the process engine.
     *
     * @return bool - true if this is a primitive valued type. False otherwise
     */
    public function isPrimitiveValueType(): bool;

    /**
     * Get the value info (meta data) for a TypedValue.
     * The keys of the returned map for a TypedValue are available
     * as constants in the value's ValueType interface.
     *
     * @param typedValue
     * @return
     */
    public function getValueInfo(TypedValueInterface $typedValue): array;

    /**
     * Creates a new TypedValue using this type.
     * @param value the value
     * @return TypedValueInterface the typed value for the value
     */
    public function createValue($value, ?array $valueInfo = null): TypedValueInterface;

    /**
     * <p>Gets the parent value type.</p>
     *
     * <p>Value type hierarchy is only relevant for queries and has the
     * following meaning: When a value query is made
     * (e.g. all tasks with a certain variable value), a "child" type's value
     * also matches a parameter value of the parent type. This is only
     * supported when the parent value type's implementation of {@link #isAbstract()}
     * returns <code>true</code>.</p>
     */
    public function getParent(): ?ValueTypeInterface;

    /**
     * Determines whether the argument typed value can be converted to a
     * typed value of this value type.
     */
    public function canConvertFromTypedValue(?TypedValueInterface $typedValue): bool;

    /**
     * Converts a typed value to a typed value of this type.
     * This does not suceed if {@link #canConvertFromTypedValue(TypedValue)}
     * returns <code>false</code>.
     */
    public function convertFromTypedValue(TypedValueInterface $typedValue): TypedValueInterface;

    /**
     * <p>Returns whether the value type is abstract. This is <b>not related
     * to the term <i>abstract</i> in the Java language.</b></p>
     *
     * Abstract value types cannot be used as types for variables but only used for querying.
     */
    public function isAbstract(): bool;
}
