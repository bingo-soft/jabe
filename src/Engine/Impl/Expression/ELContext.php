<?php

namespace BpmPlatform\Engine\Impl\Expression;

abstract class ELContext
{
    private $context = [];
    private $locale;
    private $resolved;

    /**
     * Returns the context object associated with the given key. The ELContext maintains a
     * collection of context objects relevant to the evaluation of an expression. These context
     * objects are used by ELResolvers. This method is used to retrieve the context with the given
     * key from the collection. By convention, the object returned will be of the type specified by
     * the key. However, this is not required and the key is used strictly as a unique identifier.
     *
     * @param key
     *            The unique identifier that was used to associate the context object with this
     *            ELContext.
     * @return The context object associated with the given key, or null if no such context was
     *         found.
     * @throws NullPointerException
     *             if key is null.
     */
    public function getContext($key)
    {
        return $this->context[$key];
    }

    /**
     * Retrieves the ELResolver associated with this context. The ELContext maintains a reference to
     * the ELResolver that will be consulted to resolve variables and properties during an
     * expression evaluation. This method retrieves the reference to the resolver. Once an ELContext
     * is constructed, the reference to the ELResolver associated with the context cannot be
     * changed.
     *
     * @return The resolver to be consulted for variable and property resolution during expression
     *         evaluation.
     */
    abstract public function getELResolver(): ELResolver;

    /**
     * Retrieves the FunctionMapper associated with this ELContext.
     *
     * @return The function mapper to be consulted for the resolution of EL functions.
     */
    abstract public function getFunctionMapper(): FunctionMapper;

    /**
     * Get the Locale stored by a previous invocation to {@link #setLocale(Locale)}. If this method
     * returns non null, this Locale must be used for all localization needs in the implementation.
     * The Locale must not be cached to allow for applications that change Locale dynamically.
     *
     * @return The Locale in which this instance is operating. Used primarily for message
     *         localization.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Retrieves the VariableMapper associated with this ELContext.
     *
     * @return The variable mapper to be consulted for the resolution of EL variables.
     */
    abstract public function getVariableMapper(): VariableMapper;

    /**
     * Returns whether an {@link ELResolver} has successfully resolved a given (base, property)
     * pair. The {@link CompositeELResolver} checks this property to determine whether it should
     * consider or skip other component resolvers.
     *
     * @return The variable mapper to be consulted for the resolution of EL variables.
     * @see CompositeELResolver
     */
    public function isPropertyResolved(): bool
    {
        return $this->resolved;
    }

    /**
     * Associates a context object with this ELContext. The ELContext maintains a collection of
     * context objects relevant to the evaluation of an expression. These context objects are used
     * by ELResolvers. This method is used to add a context object to that collection. By
     * convention, the contextObject will be of the type specified by the key. However, this is not
     * required and the key is used strictly as a unique identifier.
     *
     * @param key
     *            The key used by an {@link ELResolver} to identify this context object.
     * @param contextObject
     *            The context object to add to the collection.
     * @throws NullPointerException
     *             if key is null or contextObject is null.
     */
    public function putContext($key, $contextObject): void
    {
        $this->context[$key] = $contextObject;
    }

    /**
     * Set the Locale for this instance. This method may be called by the party creating the
     * instance, such as JavaServer Faces or JSP, to enable the EL implementation to provide
     * localized messages to the user. If no Locale is set, the implementation must use the locale
     * returned by Locale.getDefault( ).
     *
     * @param locale
     *            The Locale in which this instance is operating. Used primarily for message
     *            localization.
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Called to indicate that a ELResolver has successfully resolved a given (base, property) pair.
     * The {@link CompositeELResolver} checks this property to determine whether it should consider
     * or skip other component resolvers.
     *
     * @param resolved
     *            true if the property has been resolved, or false if not.
     * @see CompositeELResolver
     */
    public function setPropertyResolved(bool $resolved): void
    {
        $this->resolved = $resolved;
    }
}
