<?php

namespace Jabe\Engine\Impl\Util\Scripting;

interface ScriptContextInterface
{
    /**
     * EngineScope attributes are visible during the lifetime of a single
     * <code>ScriptEngine</code> and a set of attributes is maintained for each
     * engine.
     */
    public const ENGINE_SCOPE = 100;

    /**
     * GlobalScope attributes are visible to all engines created by same ScriptEngineFactory.
     */
    public const GLOBAL_SCOPE = 200;

    /**
     * Associates a <code>Bindings</code> instance with a particular scope in this
     * <code>ScriptContext</code>.  Calls to the <code>getAttribute</code> and
     * <code>setAttribute</code> methods must map to the <code>get</code> and
     * <code>put</code> methods of the <code>Bindings</code> for the specified scope.
     *
     * @param  bindings The <code>Bindings</code> to associate with the given scope
     * @param scope The scope
     *
     * @throws IllegalArgumentException If no <code>Bindings</code> is defined for the
     * specified scope value in ScriptContexts of this type.
     * @throws NullPointerException if value of scope is <code>ENGINE_SCOPE</code> and
     * the specified <code>Bindings</code> is null.
     *
     */
    public function setBindings(BindingsInterface $bindings, int $scope): void;

    /**
     * Gets the <code>Bindings</code>  associated with the given scope in this
     * <code>ScriptContext</code>.
     *
     * @return BindingsInterface The associated <code>Bindings</code>.  Returns <code>null</code> if it has not
     * been set.
     *
     * @param scope The scope
     * @throws IllegalArgumentException If no <code>Bindings</code> is defined for the
     * specified scope value in <code>ScriptContext</code> of this type.
     */
    public function getBindings(int $scope): ?BindingsInterface;

    /**
     * Sets the value of an attribute in a given scope.
     *
     * @param name The name of the attribute to set
     * @param value The value of the attribute
     * @param scope The scope in which to set the attribute
     *
     * @throws IllegalArgumentException
     *         if the name is empty or if the scope is invalid.
     * @throws NullPointerException if the name is null.
     */
    public function setAttribute(string $name, $value, int $scope): void;

    /**
     * Gets the value of an attribute in a given scope.
     *
     * @param name The name of the attribute to retrieve.
     * @param scope The scope in which to retrieve the attribute.
     * @return mixed The value of the attribute. Returns <code>null</code> is the name
     * does not exist in the given scope.
     *
     * @throws IllegalArgumentException
     *         if the name is empty or if the value of scope is invalid.
     * @throws NullPointerException if the name is null.
     */
    public function getAttribute(string $name, ?int $scope = null);

    /**
     * Remove an attribute in a given scope.
     *
     * @param name The name of the attribute to remove
     * @param scope The scope in which to remove the attribute
     *
     * @return mixed The removed value.
     * @throws IllegalArgumentException
     *         if the name is empty or if the scope is invalid.
     * @throws NullPointerException if the name is null.
     */
    public function removeAttribute(string $name, int $scope);

    /**
     * Get the lowest scope in which an attribute is defined.
     * @param name Name of the attribute
     * .
     * @return mixed The lowest scope.  Returns -1 if no attribute with the given
     * name is defined in any scope.
     * @throws NullPointerException if name is null.
     * @throws IllegalArgumentException if name is empty.
     */
    public function getAttributesScope(string $name);

    /**
     * Returns immutable <code>List</code> of all the valid values for
     * scope in the ScriptContext.
     *
     * @return list of scope values
     */
    public function getScopes(): array;
}
