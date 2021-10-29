<?php

namespace BpmPlatform\Engine\Impl\Language;

class Feature
{
    /**
     * Method invocations as in <code>${foo.bar(1)}</code> as specified in JSR 245,
     * maintenance release 2.
     * The method to be invoked is resolved at evaluation time by calling
     * {@link ELResolver#invoke(javax.el.ELContext, Object, Object, Class[], Object[])}.
     */
    public const METHOD_INVOCATIONS = "method_invocations";
    /**
     * For some reason we don't understand, the specification does not allow to resolve
     * <code>null</code> property values. E.g. <code>${map[key]}</code> will always
     * return <code>null</code> if <code>key</code> evaluates to <code>null</code>.
     * Enabling this feature will allow <em>JUEL</em> to pass <code>null</code> to
     * the property resolvers just like any other property value.
     */
    public const NULL_PROPERTIES = "null_properties";
    /**
     * Allow for use of varargs in function calls.
     */
    public const VARARGS = "varargs";
}
