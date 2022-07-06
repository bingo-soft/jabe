<?php

namespace Jabe\Engine\Impl\Util\El;

class ArrayELResolver extends ELResolver
{
    private $readOnly;

    /**
     * Creates a new ArrayELResolver whose read-only status is determined by the given parameter.
     *
     * @param readOnly
     *            true if this resolver cannot modify arrays; false otherwise.
     */
    public function __construct(?bool $readOnly = false)
    {
        $this->readOnly = $readOnly;
    }

    /**
     * If the base object is a Java language array, returns the most general type that this resolver
     * accepts for the property argument. Otherwise, returns null. Assuming the base is an array,
     * this method will always return Integer.class. This is because arrays accept integers for
     * their index.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The array to analyze. Only bases that are a Java language array are handled by
     *            this resolver.
     * @return string null if base is not a Java language array; otherwise Integer.class.
     */
    public function getCommonPropertyType(?ELContext $context, $base): ?string
    {
        return $this->isResolvable($base) ? gettype(1) : null;
    }

    /**
     * Always returns null, since there is no reason to iterate through set set of all integers. The
     * getCommonPropertyType(ELContext, Object)8 method returns sufficient information about what
     * properties this resolver accepts.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The array to analyze. Only bases that are a Java language array are handled by
     *            this resolver.
     * @return array null.
     */
    public function getFeatureDescriptors(?ELContext $context, $base): ?array
    {
        return null;
    }

    /**
     * If the base object is an array, returns the most general acceptable type for a value in this
     * array. If the base is a array, the propertyResolved property of the ELContext object must be
     * set to true by this resolver, before returning. If this property is not true after this
     * method is called, the caller should ignore the return value. Assuming the base is an array,
     * this method will always return base.getClass().getComponentType(), which is the most general
     * type of component that can be stored at any given index in the array.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The array to analyze. Only bases that are a Java language array are handled by
     *            this resolver.
     * @param property
     *            The index of the element in the array to return the acceptable type for. Will be
     *            coerced into an integer, but otherwise ignored by this resolver.
     * @return If the propertyResolved property of ELContext was set to true, then the most general
     *         acceptable type; otherwise undefined.
     * @throws PropertyNotFoundException
     *             if the given index is out of bounds for this array.
     * @throws NullPointerException
     *             if context is null
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function getType(?ELContext $context, $base, $property)
    {
        if ($context === null) {
            throw new \Exception("context is null");
        }
        $result = null;
        if ($this->isResolvable($base)) {
            $this->toIndex($base, $property);
            $result = gettype(new \stdClass());
            $context->setPropertyResolved(true);
        }
        return $result;
    }

    /**
     * If the base object is a Java language array, returns the value at the given index. The index
     * is specified by the property argument, and coerced into an integer. If the coercion could not
     * be performed, an IllegalArgumentException is thrown. If the index is out of bounds, null is
     * returned. If the base is a Java language array, the propertyResolved property of the
     * ELContext object must be set to true by this resolver, before returning. If this property is
     * not true after this method is called, the caller should ignore the return value.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The array to analyze. Only bases that are a Java language array are handled by
     *            this resolver.
     * @param property
     *            The index of the element in the array to return the acceptable type for. Will be
     *            coerced into an integer, but otherwise ignored by this resolver.
     * @return If the propertyResolved property of ELContext was set to true, then the value at the
     *         given index or null if the index was out of bounds. Otherwise, undefined.
     * @throws PropertyNotFoundException
     *             if the given index is out of bounds for this array.
     * @throws NullPointerException
     *             if context is null
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function getValue(?ELContext $context, $base, $property)
    {
        if ($context === null) {
            throw new \Exception("context is null");
        }
        $result = null;
        if ($this->isResolvable($base)) {
            $index = $this->toIndex(null, $property);
            $result = $index < 0 || $index >= count($base) ? null : $base[$index];
            $context->setPropertyResolved(true);
        }
        return $result;
    }

    /**
     * If the base object is a Java language array, returns whether a call to
     * {@link #setValue(ELContext, Object, Object, Object)} will always fail. If the base is a Java
     * language array, the propertyResolved property of the ELContext object must be set to true by
     * this resolver, before returning. If this property is not true after this method is called,
     * the caller should ignore the return value. If this resolver was constructed in read-only
     * mode, this method will always return true. Otherwise, it returns false.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The array to analyze. Only bases that are a Java language array are handled by
     *            this resolver.
     * @param property
     *            The index of the element in the array to return the acceptable type for. Will be
     *            coerced into an integer, but otherwise ignored by this resolver.
     * @return If the propertyResolved property of ELContext was set to true, then true if calling
     *         the setValue method will always fail or false if it is possible that such a call may
     *         succeed; otherwise undefined.
     * @throws PropertyNotFoundException
     *             if the given index is out of bounds for this array.
     * @throws NullPointerException
     *             if context is null
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        if ($context === null) {
            throw new \Exception("context is null");
        }
        if ($this->isResolvable($base)) {
            $this->toIndex($base, $property);
            $context->setPropertyResolved(true);
        }
        return $this->readOnly;
    }

    /**
     * If the base object is a Java language array, attempts to set the value at the given index
     * with the given value. The index is specified by the property argument, and coerced into an
     * integer. If the coercion could not be performed, an IllegalArgumentException is thrown. If
     * the index is out of bounds, a PropertyNotFoundException is thrown. If the base is a Java
     * language array, the propertyResolved property of the ELContext object must be set to true by
     * this resolver, before returning. If this property is not true after this method is called,
     * the caller can safely assume no value was set. If this resolver was constructed in read-only
     * mode, this method will always throw PropertyNotWritableException.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The array to analyze. Only bases that are a Java language array are handled by
     *            this resolver.
     * @param property
     *            The index of the element in the array to return the acceptable type for. Will be
     *            coerced into an integer, but otherwise ignored by this resolver.
     * @param value
     *            The value to be set at the given index.
     * @throws PropertyNotFoundException
     *             if the given index is out of bounds for this array.
     * @throws ClassCastException
     *             if the class of the specified element prevents it from being added to this array.
     * @throws NullPointerException
     *             if context is null
     * @throws IllegalArgumentException
     *             if the property could not be coerced into an integer, or if some aspect of the
     *             specified element prevents it from being added to this array.
     * @throws PropertyNotWritableException
     *             if this resolver was constructed in read-only mode.
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function setValue(?ELContext $context, $base, $property, $value): void
    {
        if ($context === null) {
            throw new \Exception("context is null");
        }
        if ($this->isResolvable($base)) {
            if ($this->readOnly) {
                throw new PropertyNotWritableException("resolver is read-only");
            }
            $base[$this->toIndex($base, $property)] = $value;
            $context->setPropertyResolved(true);
        }
    }

    /**
     * Test whether the given base should be resolved by this ELResolver.
     *
     * @param base
     *            The bean to analyze.
     * @param property
     *            The name of the property to analyze. Will be coerced to a String.
     * @return base !== null && base.getClass().isArray()
     */
    private function isResolvable($base = null): bool
    {
        return $base !== null && is_array($base);
    }

    /**
     * Convert the given property to an index in (array) base.
     *
     * @param base
     *            The bean to analyze.
     * @param property
     *            The name of the property to analyze. Will be coerced to a String.
     * @return int The index of property in base.
     * @throws IllegalArgumentException
     *             if base property cannot be coerced to an integer or base is not an array.
     * @throws PropertyNotFoundException
     *             if the computed index is out of bounds for base.
     */
    private function toIndex(&$base, $property): int
    {
        $index = 0;
        if (is_numeric($property)) {
            $index = intval($property);
        }
        if ($base !== null && ($index < 0 || $index >= count($base))) {
            throw new PropertyNotFoundException("Array index out of bounds: " . $index);
        }
        return $index;
    }
}
