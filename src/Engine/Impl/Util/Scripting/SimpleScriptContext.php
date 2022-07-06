<?php

namespace Jabe\Engine\Impl\Util\Scripting;

class SimpleScriptContext implements ScriptContextInterface
{
    /**
     * This is the engine scope bindings.
     * By default, a <code>SimpleBindings</code> is used. Accessor
     * methods setBindings, getBindings are used to manage this field.
     * @see SimpleBindings
     */
    protected $engineScope;

    /**
     * This is the global scope bindings.
     * By default, a null value (which means no global scope) is used. Accessor
     * methods setBindings, getBindings are used to manage this field.
     */
    protected $globalScope;

    /**
     * Create a {@code SimpleScriptContext}.
     */
    public function __construct()
    {
        $this->engineScope = new SimpleBindings();
        $this->globalScope = null;
        if (self::$scopes === null) {
            self::$scopes = [];
            self::$scopes[] = self::ENGINE_SCOPE;
            self::$scopes[] = self::GLOBAL_SCOPE;
        }
    }

    /**
     * Sets a <code>Bindings</code> of attributes for the given scope.  If the value
     * of scope is <code>ENGINE_SCOPE</code> the given <code>Bindings</code> replaces the
     * <code>engineScope</code> field.  If the value
     * of scope is <code>GLOBAL_SCOPE</code> the given <code>Bindings</code> replaces the
     * <code>globalScope</code> field.
     *
     * @param bindings The <code>Bindings</code> of attributes to set.
     * @param scope The value of the scope in which the attributes are set.
     *
     * @throws IllegalArgumentException if scope is invalid.
     * @throws NullPointerException if the value of scope is <code>ENGINE_SCOPE</code> and
     * the specified <code>Bindings</code> is null.
     */
    public function setBindings(BindingsInterface $bindings, int $scope): void
    {
        switch ($scope) {
            case self::ENGINE_SCOPE:
                $this->engineScope = $bindings;
                break;
            case self::GLOBAL_SCOPE:
                $this->globalScope = $bindings;
                break;
            default:
                throw new \Exception("Invalid scope value.");
        }
    }


    /**
     * Retrieves the value of the attribute with the given name in
     * the scope occurring earliest in the search order.  The order
     * is determined by the numeric value of the scope parameter (lowest
     * scope values first.)
     *
     * @param name The name of the the attribute to retrieve.
     * @return mixed The value of the attribute in the lowest scope for
     * which an attribute with the given name is defined.  Returns
     * null if no attribute with the name exists in any scope.
     * @throws NullPointerException if the name is null.
     * @throws IllegalArgumentException if the name is empty.
     */
    public function getAttribute(string $name, ?int $scope = null)
    {
        $this->checkName($name);
        if ($scope !== null) {
            switch ($scope) {
                case self::ENGINE_SCOPE:
                    return $this->engineScope->get($name);
                case self::GLOBAL_SCOPE:
                    if ($this->globalScope !== null) {
                        return $this->globalScope->get($name);
                    }
                    return null;
                default:
                    throw new \Exception("Illegal scope value.");
            }
        }
        if ($this->engineScope->containsKey($name)) {
            return $this->getAttribute($name, self::ENGINE_SCOPE);
        } elseif ($this->globalScope !== null && $this->globalScope->containsKey($name)) {
            return $this->getAttribute($name, self::GLOBAL_SCOPE);
        }

        return null;
    }

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
    public function removeAttribute(string $name, int $scope)
    {
        $this->checkName($name);
        switch ($scope) {
            case self::ENGINE_SCOPE:
                if ($this->getBindings(self::ENGINE_SCOPE) !== null) {
                    return $this->getBindings(self::ENGINE_SCOPE)->remove($name);
                }
                return null;
            case self::GLOBAL_SCOPE:
                if ($this->getBindings(self::GLOBAL_SCOPE) !== null) {
                    return $this->getBindings(self::GLOBAL_SCOPE)->remove($name);
                }
                return null;
            default:
                throw new \Exception("Illegal scope value.");
        }
    }

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
    public function setAttribute(string $name, $value, int $scope): void
    {
        $this->checkName($name);
        switch ($scope) {
            case self::ENGINE_SCOPE:
                $this->engineScope->put($name, $value);
                return;
            case self::GLOBAL_SCOPE:
                if ($this->globalScope !== null) {
                    $this->globalScope->put($name, $value);
                }
                return;
            default:
                throw new IllegalArgumentException("Illegal scope value.");
        }
    }

   /**
     * Get the lowest scope in which an attribute is defined.
     * @param name Name of the attribute
     * .
     * @return int The lowest scope.  Returns -1 if no attribute with the given
     * name is defined in any scope.
     * @throws NullPointerException if name is null.
     * @throws IllegalArgumentException if name is empty.
     */
    public function getAttributesScope(string $name): int
    {
        $this->checkName($name);
        if ($this->engineScope->containsKey($name)) {
            return self::ENGINE_SCOPE;
        } elseif ($this->globalScope !== null && $this->globalScope->containsKey($name)) {
            return self::GLOBAL_SCOPE;
        } else {
            return -1;
        }
    }

    /**
     * Returns the value of the <code>engineScope</code> field if specified scope is
     * <code>ENGINE_SCOPE</code>.  Returns the value of the <code>globalScope</code> field if the specified scope is
     * <code>GLOBAL_SCOPE</code>.
     *
     * @param scope The specified scope
     * @return BindingsInterface The value of either the  <code>engineScope</code> or <code>globalScope</code> field.
     * @throws IllegalArgumentException if the value of scope is invalid.
     */
    public function getBindings(int $scope): BindingsInterface
    {
        if ($scope == self::ENGINE_SCOPE) {
            return $this->engineScope;
        } elseif ($scope == self::GLOBAL_SCOPE) {
            return $this->globalScope;
        } else {
            throw new \Exception("Illegal scope value.");
        }
    }

    /** {@inheritDoc} */
    public function getScopes(): array
    {
        return self::$scopes;
    }

    private function checkName(string $name)
    {
        if (empty($name)) {
            throw new \Exception("name cannot be empty");
        }
    }

    private static $scopes = null;
}
