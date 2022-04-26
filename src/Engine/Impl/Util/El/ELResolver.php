<?php

namespace Jabe\Engine\Impl\Util\El;

abstract class ELResolver
{
    /**
     * The attribute name of the named attribute in the FeatureDescriptor that specifies whether the
     * variable or property can be resolved at runtime.
     */
    public const RESOLVABLE_AT_DESIGN_TIME = "resolvableAtDesignTime";

    /**
     * The attribute name of the named attribute in the FeatureDescriptor that specifies the runtime
     * type of the variable or property.
     */
    public const TYPE = "type";

    /**
     * Returns the most general type that this resolver accepts for the property argument, given a
     * base object. One use for this method is to assist tools in auto-completion. This assists
     * tools in auto-completion and also provides a way to express that the resolver accepts a
     * primitive value, such as an integer index into an array. For example, the
     * {@link ArrayELResolver} will accept any int as a property, so the return value would be
     * Integer.class.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object to return the most general property type for, or null to enumerate
     *            the set of top-level variables that this resolver can evaluate.
     * @return null if this ELResolver does not know how to handle the given base object; otherwise
     *         Object.class if any type of property is accepted; otherwise the most general property
     *         type accepted for the given base.
     */
    abstract public function getCommonPropertyType(?ELContext $context, $base): ?string;

    /**
     * Returns information about the set of variables or properties that can be resolved for the
     * given base object. One use for this method is to assist tools in auto-completion. If the base
     * parameter is null, the resolver must enumerate the list of top-level variables it can
     * resolve. The Iterator returned must contain zero or more instances of
     * java.beans.FeatureDescriptor, in no guaranteed order. In the case of primitive types such as
     * int, the value null must be returned. This is to prevent the useless iteration through all
     * possible primitive values. A return value of null indicates that this resolver does not
     * handle the given base object or that the results are too complex to represent with this
     * method and the {@link #getCommonPropertyType(ELContext, Object)} method should be used
     * instead. Each FeatureDescriptor will contain information about a single variable or property.
     * In addition to the standard properties, the FeatureDescriptor must have two named attributes
     * (as set by the setValue method):
     * <ul>
     * <li>{@link #TYPE} - The value of this named attribute must be an instance of java.lang.Class
     * and specify the runtime type of the variable or property.</li>
     * <li>{@link #RESOLVABLE_AT_DESIGN_TIME} - The value of this named attribute must be an
     * instance of java.lang.Boolean and indicates whether it is safe to attempt to resolve this
     * property at designtime. For instance, it may be unsafe to attempt a resolution at design time
     * if the ELResolver needs access to a resource that is only available at runtime and no
     * acceptable simulated value can be provided.</li>
     * </ul>
     * The caller should be aware that the Iterator returned might iterate through a very large or
     * even infinitely large set of properties. Care should be taken by the caller to not get stuck
     * in an infinite loop. This is a "best-effort" list. Not all ELResolvers will return completely
     * accurate results, but all must be callable at both design-time and runtime (i.e. whether or
     * not Beans.isDesignTime() returns true), without causing errors. The propertyResolved property
     * of the ELContext is not relevant to this method. The results of all ELResolvers are
     * concatenated in the case of composite resolvers.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object whose set of valid properties is to be enumerated, or null to
     *            enumerate the set of top-level variables that this resolver can evaluate.
     * @return An Iterator containing zero or more (possibly infinitely more) FeatureDescriptor
     *         objects, or null if this resolver does not handle the given base object or that the
     *         results are too complex to represent with this method
     */
    abstract public function getFeatureDescriptors(?ELContext $context, $base): ?array;

    /**
     * For a given base and property, attempts to identify the most general type that is acceptable
     * for an object to be passed as the value parameter in a future call to the
     * {@link #setValue(ELContext, Object, Object, Object)} method. If this resolver handles the
     * given (base, property) pair, the propertyResolved property of the ELContext object must be
     * set to true by the resolver, before returning. If this property is not true after this method
     * is called, the caller should ignore the return value. This is not always the same as
     * getValue().getClass(). For example, in the case of an {@link ArrayELResolver}, the getType
     * method will return the element type of the array, which might be a superclass of the type of
     * the actual element that is currently in the specified array element.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object whose property value is to be analyzed, or null to analyze a
     *            top-level variable.
     * @param property
     *            The property or variable to return the acceptable type for.
     * @return If the propertyResolved property of ELContext was set to true, then the most general
     *         acceptable type; otherwise undefined.
     * @throws java.lang.NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if the given (base, property) pair is handled by this ELResolver but the
     *             specified variable or property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function getType(?ELContext $context, $base, $property);

    /**
     * Attempts to resolve the given property object on the given base object. If this resolver
     * handles the given (base, property) pair, the propertyResolved property of the ELContext
     * object must be set to true by the resolver, before returning. If this property is not true
     * after this method is called, the caller should ignore the return value.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object whose property value is to be returned, or null to resolve a
     *            top-level variable.
     * @param property
     *            The property or variable to be resolved.
     * @return If the propertyResolved property of ELContext was set to true, then the result of the
     *         variable or property resolution; otherwise undefined.
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if the given (base, property) pair is handled by this ELResolver but the
     *             specified variable or property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function getValue(?ELContext $context, $base, $property);

    /**
     * For a given base and property, attempts to determine whether a call to
     * {@link #setValue(ELContext, Object, Object, Object)} will always fail. If this resolver
     * handles the given (base, property) pair, the propertyResolved property of the ELContext
     * object must be set to true by the resolver, before returning. If this property is not true
     * after this method is called, the caller should ignore the return value.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object whose property value is to be analyzed, or null to analyze a
     *            top-level variable.
     * @param property
     *            The property or variable to return the read-only status for.
     * @return If the propertyResolved property of ELContext was set to true, then true if the
     *         property is read-only or false if not; otherwise undefined.
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if the given (base, property) pair is handled by this ELResolver but the
     *             specified variable or property does not exist.
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function isReadOnly(?ELContext $context, $base, $property): bool;

    /**
     * Attempts to set the value of the given property object on the given base object. If this
     * resolver handles the given (base, property) pair, the propertyResolved property of the
     * ELContext object must be set to true by the resolver, before returning. If this property is
     * not true after this method is called, the caller can safely assume no value has been set.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object whose property value is to be set, or null to set a top-level
     *            variable.
     * @param property
     *            The property or variable to be set.
     * @param value
     *            The value to set the property or variable to.
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if the given (base, property) pair is handled by this ELResolver but the
     *             specified variable or property does not exist.
     * @throws PropertyNotWritableException
     *             if the given (base, property) pair is handled by this ELResolver but the
     *             specified variable or property is not writable.
     * @throws ELException
     *             if an exception was thrown while attempting to set the property or variable. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function setValue(?ELContext $context, $base, $property, $value): void;

    /**
     * Attempts to resolve and invoke the given <code>method</code> on the given <code>base</code>
     * object.
     *
     * <p>
     * If this resolver handles the given (base, method) pair, the <code>propertyResolved</code>
     * property of the <code>ELContext</code> object must be set to <code>true</code> by the
     * resolver, before returning. If this property is not <code>true</code> after this method is
     * called, the caller should ignore the return value.
     * </p>
     *
     * <p>
     * A default implementation is provided that returns null so that existing classes that extend
     * ELResolver can continue to function.
     * </p>
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The bean on which to invoke the method
     * @param method
     *            The simple name of the method to invoke. Will be coerced to a <code>String</code>.
     * @param paramTypes
     *            An array of Class objects identifying the method's formal parameter types, in
     *            declared order. Use an empty array if the method has no parameters. Can be
     *            <code>null</code>, in which case the method's formal parameter types are assumed
     *            to be unknown.
     * @param params
     *            The parameters to pass to the method, or <code>null</code> if no parameters.
     * @return The result of the method invocation (<code>null</code> if the method has a
     *         <code>void</code> return type).
     * @throws MethodNotFoundException
     *             if no suitable method can be found.
     * @throws ELException
     *             if an exception was thrown while performing (base, method) resolution. The thrown
     *             exception must be included as the cause property of this exception, if available.
     *             If the exception thrown is an <code>InvocationTargetException</code>, extract its
     *             <code>cause</code> and pass it to the <code>ELException</code> constructor.
     * @since 2.2
     */
    public function invoke(?ELContext $context, $base, $method, ?array $paramTypes = [], ?array $params = [])
    {
        return null;
    }
}
