<?php

namespace BpmPlatform\Engine\Impl\Expression;

abstract class ValueExpression extends Expression
{
    /**
     * Returns the type the result of the expression will be coerced to after evaluation.
     *
     * @return the expectedType passed to the ExpressionFactory.createValueExpression method that
     *         created this ValueExpression.
     */
    abstract public function getExpectedType(): string;

    /**
     * Evaluates the expression relative to the provided context, and returns the most general type
     * that is acceptable for an object to be passed as the value parameter in a future call to the
     * {@link #setValue(ELContext, Object)} method. This is not always the same as
     * getValue().getClass(). For example, in the case of an expression that references an array
     * element, the getType method will return the element type of the array, which might be a
     * superclass of the type of the actual element that is currently in the specified array
     * element.
     *
     * @param context
     *            The context of this evaluation.
     * @return the most general acceptable type; otherwise undefined.
     * @throws NullPointerException
     *             if context is null.
     * @throws PropertyNotFoundException
     *             if one of the property resolutions failed because a specified variable or
     *             property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing property or variable resolution. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function getType(ELContext $context): ?string;

    /**
     * Evaluates the expression relative to the provided context, and returns the resulting value.
     * The resulting value is automatically coerced to the type returned by getExpectedType(), which
     * was provided to the ExpressionFactory when this expression was created.
     *
     * @param context
     *            The context of this evaluation.
     * @return The result of the expression evaluation.
     * @throws NullPointerException
     *             if context is null.
     * @throws PropertyNotFoundException
     *             if one of the property resolutions failed because a specified variable or
     *             property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing property or variable resolution. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function getValue(ELContext $context);

    /**
     * Evaluates the expression relative to the provided context, and returns true if a call to
     * {@link #setValue(ELContext, Object)} will always fail.
     *
     * @param context
     *            The context of this evaluation.
     * @return true if the expression is read-only or false if not.
     * @throws NullPointerException
     *             if context is null.
     * @throws PropertyNotFoundException
     *             if one of the property resolutions failed because a specified variable or
     *             property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing property or variable resolution. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function isReadOnly(ELContext $context): bool;

    /**
     * Evaluates the expression relative to the provided context, and sets the result to the
     * provided value.
     *
     * @param context
     *            The context of this evaluation.
     * @param value
     *            The new value to be set.
     * @throws NullPointerException
     *             if context is null.
     * @throws PropertyNotFoundException
     *             if one of the property resolutions failed because a specified variable or
     *             property does not exist or is not readable.
     * @throws PropertyNotWritableException
     *             if the final variable or property resolution failed because the specified
     *             variable or property is not writable.
     * @throws ELException
     *             if an exception was thrown while attempting to set the property or variable. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function setValue(ELContext $context, $value): void;

    /**
     * Returns a {@link ValueReference} for this expression instance.
     *
     * @param context
     *            the context of this evaluation
     * @return the <code>ValueReference</code> for this <code>ValueExpression</code>, or
     *         <code>null</code> if this <code>ValueExpression</code> is not a reference to a base
     *         (null or non-null) and a property. If the base is null, and the property is a EL
     *         variable, return the <code>ValueReference</code> for the <code>ValueExpression</code>
     *         associated with this EL variable.
     *
     * @since 2.2
     */
    public function getValueReference(ELContext $context): ?ValueReference
    {
        return null;
    }
}
