<?php

namespace Jabe\Engine\Impl\Util\El;

class CompositeELResolver extends ELResolver
{
    private $resolvers = [];

    /**
     * Adds the given resolver to the list of component resolvers. Resolvers are consulted in the
     * order in which they are added.
     *
     * @param elResolver
     *            The component resolver to add.
     * @throws NullPointerException
     *             If the provided resolver is null.
     */
    public function add(?ELResolver $elResolver): void
    {
        if ($elResolver == null) {
            throw new \Exception("resolver must not be null");
        }
        $this->resolvers[] = $elResolver;
    }

    /**
     * Returns the most general type that this resolver accepts for the property argument, given a
     * base object. One use for this method is to assist tools in auto-completion. The result is
     * obtained by querying all component resolvers. The Class returned is the most specific class
     * that is a common superclass of all the classes returned by each component resolver's
     * getCommonPropertyType method. If null is returned by a resolver, it is skipped.
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
    public function getCommonPropertyType(?ELContext $context, $base): ?string
    {
        $result = null;
        foreach ($this->resolvers as $resolver) {
            $type = $resolver->getCommonPropertyType($context, $base);
            if ($type != null) {
                if ($result == null || gettype($result) == $type) {
                    $result = $type;
                } elseif (gettype($result) != $type) {
                    $result = gettype(new \stdClass());
                }
            }
        }
        return $result;
    }

    /**
     * Returns information about the set of variables or properties that can be resolved for the
     * given base object. One use for this method is to assist tools in auto-completion. The results
     * are collected from all component resolvers. The propertyResolved property of the ELContext is
     * not relevant to this method. The results of all ELResolvers are concatenated. The Iterator
     * returned is an iterator over the collection of FeatureDescriptor objects returned by the
     * iterators returned by each component resolver's getFeatureDescriptors method. If null is
     * returned by a resolver, it is skipped.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object to return the most general property type for, or null to enumerate
     *            the set of top-level variables that this resolver can evaluate.
     * @return An Iterator containing zero or more (possibly infinitely more) FeatureDescriptor
     *         objects, or null if this resolver does not handle the given base object or that the
     *         results are too complex to represent with this method
     */
    public function getFeatureDescriptors(?ELContext $context, $base): ?array
    {
        $features = [];
        foreach ($this->resolvers as $resolver) {
            $features_ = $resolver->getFeatureDescriptors();
            if (is_array($features_)) {
                $features = array_merge($features, $features_);
            }
        }
        return $features;
    }

    /**
     * For a given base and property, attempts to identify the most general type that is acceptable
     * for an object to be passed as the value parameter in a future call to the
     * {@link #setValue(ELContext, Object, Object, Object)} method. The result is obtained by
     * querying all component resolvers. If this resolver handles the given (base, property) pair,
     * the propertyResolved property of the ELContext object must be set to true by the resolver,
     * before returning. If this property is not true after this method is called, the caller should
     * ignore the return value. First, propertyResolved is set to false on the provided ELContext.
     * Next, for each component resolver in this composite:
     * <ol>
     * <li>The getType() method is called, passing in the provided context, base and property.</li>
     * <li>If the ELContext's propertyResolved flag is false then iteration continues.</li>
     * <li>Otherwise, iteration stops and no more component resolvers are considered. The value
     * returned by getType() is returned by this method.</li>
     * </ol>
     * If none of the component resolvers were able to perform this operation, the value null is
     * returned and the propertyResolved flag remains set to false. Any exception thrown by
     * component resolvers during the iteration is propagated to the caller of this method.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object to return the most general property type for, or null to enumerate
     *            the set of top-level variables that this resolver can evaluate.
     * @param property
     *            The property or variable to return the acceptable type for.
     * @return If the propertyResolved property of ELContext was set to true, then the most general
     *         acceptable type; otherwise undefined.
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if base is not null and the specified property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function getType(?ELContext $context, $base, $property)
    {
        $context->setPropertyResolved(false);
        foreach ($this->resolvers as $resolver) {
            $type = $resolver->getType($context, $base, $property);
            if ($context->isPropertyResolved()) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Attempts to resolve the given property object on the given base object by querying all
     * component resolvers. If this resolver handles the given (base, property) pair, the
     * propertyResolved property of the ELContext object must be set to true by the resolver, before
     * returning. If this property is not true after this method is called, the caller should ignore
     * the return value. First, propertyResolved is set to false on the provided ELContext. Next,
     * for each component resolver in this composite:
     * <ol>
     * <li>The getValue() method is called, passing in the provided context, base and property.</li>
     * <li>If the ELContext's propertyResolved flag is false then iteration continues.</li>
     * <li>Otherwise, iteration stops and no more component resolvers are considered. The value
     * returned by getValue() is returned by this method.</li>
     * </ol>
     * If none of the component resolvers were able to perform this operation, the value null is
     * returned and the propertyResolved flag remains set to false. Any exception thrown by
     * component resolvers during the iteration is propagated to the caller of this method.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object to return the most general property type for, or null to enumerate
     *            the set of top-level variables that this resolver can evaluate.
     * @param property
     *            The property or variable to return the acceptable type for.
     * @return If the propertyResolved property of ELContext was set to true, then the result of the
     *         variable or property resolution; otherwise undefined.
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if base is not null and the specified property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function getValue(?ELContext $context, $base, $property)
    {
        $context->setPropertyResolved(false);
        foreach ($this->resolvers as $resolver) {
            $value = $resolver->getValue($context, $base, $property);
            if ($context->isPropertyResolved()) {
                return $value;
            }
        }
        return null;
    }

    /**
     * For a given base and property, attempts to determine whether a call to
     * {@link #setValue(ELContext, Object, Object, Object)} will always fail. The result is obtained
     * by querying all component resolvers. If this resolver handles the given (base, property)
     * pair, the propertyResolved property of the ELContext object must be set to true by the
     * resolver, before returning. If this property is not true after this method is called, the
     * caller should ignore the return value. First, propertyResolved is set to false on the
     * provided ELContext. Next, for each component resolver in this composite:
     * <ol>
     * <li>The isReadOnly() method is called, passing in the provided context, base and property.</li>
     * <li>If the ELContext's propertyResolved flag is false then iteration continues.</li>
     * <li>Otherwise, iteration stops and no more component resolvers are considered. The value
     * returned by isReadOnly() is returned by this method.</li>
     * </ol>
     * If none of the component resolvers were able to perform this operation, the value false is
     * returned and the propertyResolved flag remains set to false. Any exception thrown by
     * component resolvers during the iteration is propagated to the caller of this method.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object to return the most general property type for, or null to enumerate
     *            the set of top-level variables that this resolver can evaluate.
     * @param property
     *            The property or variable to return the acceptable type for.
     * @return If the propertyResolved property of ELContext was set to true, then true if the
     *         property is read-only or false if not; otherwise undefined.
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if base is not null and the specified property does not exist or is not readable.
     * @throws ELException
     *             if an exception was thrown while performing the property or variable resolution.
     *             The thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        $context->setPropertyResolved(false);
        foreach ($this->resolvers as $resolver) {
            $readOnly = $resolver->isReadOnly($context, $base, $property);
            if ($context->isPropertyResolved()) {
                return $readOnly;
            }
        }
        return false;
    }

    /**
     * Attempts to set the value of the given property object on the given base object. All
     * component resolvers are asked to attempt to set the value. If this resolver handles the given
     * (base, property) pair, the propertyResolved property of the ELContext object must be set to
     * true by the resolver, before returning. If this property is not true after this method is
     * called, the caller can safely assume no value has been set. First, propertyResolved is set to
     * false on the provided ELContext. Next, for each component resolver in this composite:
     * <ol>
     * <li>The setValue() method is called, passing in the provided context, base, property and
     * value.</li>
     * <li>If the ELContext's propertyResolved flag is false then iteration continues.</li>
     * <li>Otherwise, iteration stops and no more component resolvers are considered.</li>
     * </ol>
     * If none of the component resolvers were able to perform this operation, the propertyResolved
     * flag remains set to false. Any exception thrown by component resolvers during the iteration
     * is propagated to the caller of this method.
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The base object to return the most general property type for, or null to enumerate
     *            the set of top-level variables that this resolver can evaluate.
     * @param property
     *            The property or variable to return the acceptable type for.
     * @param value
     *            The value to set the property or variable to.
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if base is not null and the specified property does not exist or is not readable.
     * @throws PropertyNotWritableException
     *             if the given (base, property) pair is handled by this ELResolver but the
     *             specified variable or property is not writable.
     * @throws ELException
     *             if an exception was thrown while attempting to set the property or variable. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    public function setValue(?ELContext $context, $base, $property, $value): void
    {
        $context->setPropertyResolved(false);
        foreach ($this->resolvers as $resolver) {
            $resolver->setValue($context, $base, $property, $value);
            if ($context->isPropertyResolved()) {
                return;
            }
        }
    }

    /**
     * Attempts to resolve and invoke the given <code>method</code> on the given <code>base</code>
     * object by querying all component resolvers.
     *
     * <p>
     * If this resolver handles the given (base, method) pair, the <code>propertyResolved</code>
     * property of the <code>ELContext</code> object must be set to <code>true</code> by the
     * resolver, before returning. If this property is not <code>true</code> after this method is
     * called, the caller should ignore the return value.
     * </p>
     *
     * <p>
     * First, <code>propertyResolved</code> is set to <code>false</code> on the provided
     * <code>ELContext</code>.
     * </p>
     *
     * <p>
     * Next, for each component resolver in this composite:
     * <ol>
     * <li>The <code>invoke()</code> method is called, passing in the provided <code>context</code>,
     * <code>base</code>, <code>method</code>, <code>paramTypes</code>, and <code>params</code>.</li>
     * <li>If the <code>ELContext</code>'s <code>propertyResolved</code> flag is <code>false</code>
     * then iteration continues.</li>
     * <li>Otherwise, iteration stops and no more component resolvers are considered. The value
     * returned by <code>getValue()</code> is returned by this method.</li>
     * </ol>
     * </p>
     *
     * <p>
     * If none of the component resolvers were able to perform this operation, the value
     * <code>null</code> is returned and the <code>propertyResolved</code> flag remains set to
     * <code>false</code>
     * </p>
     *
     * <p>
     * Any exception thrown by component resolvers during the iteration is propagated to the caller
     * of this method.
     * </p>
     *
     * @param context
     *            The context of this evaluation.
     * @param base
     *            The bean on which to invoke the method
     * @param method
     *            The simple name of the method to invoke. Will be coerced to a <code>String</code>.
     *            If method is "&lt;init&gt;"or "&lt;clinit&gt;" a NoSuchMethodException is raised.
     * @param paramTypes
     *            An array of Class objects identifying the method's formal parameter types, in
     *            declared order. Use an empty array if the method has no parameters. Can be
     *            <code>null</code>, in which case the method's formal parameter types are assumed
     *            to be unknown.
     * @param params
     *            The parameters to pass to the method, or <code>null</code> if no parameters.
     * @return The result of the method invocation (<code>null</code> if the method has a
     *         <code>void</code> return type).
     * @since 2.2
     */
    public function invoke(?ELContext $context, $base, $method, ?array $paramTypes = [], ?array $params = [])
    {
        $context->setPropertyResolved(false);
        foreach ($this->resolvers as $resolver) {
            $result = $resolver->invoke($context, $base, $method, $paramTypes, $params);
            if ($context->isPropertyResolved()) {
                return $result;
            }
        }
        return null;
    }
}
