<?php

namespace BpmPlatform\Engine\Impl\Util\Scripting;

abstract class AbstractScriptEngine implements ScriptEngineInterface
{
/**
     * The default <code>ScriptContext</code> of this <code>AbstractScriptEngine</code>.
     */

    protected $context;

    /**
     * Creates a new instance using the specified <code>Bindings</code> as the
     * <code>ENGINE_SCOPE</code> <code>Bindings</code> in the protected <code>context</code> field.
     *
     * @param n The specified <code>Bindings</code>.
     * @throws NullPointerException if n is null.
     */
    public function __construct(BindingsInterface $n)
    {
        $this->context = new SimpleScriptContext();
        $context->setBindings($n, ScriptContextInterface::ENGINE_SCOPE);
    }

    /**
     * Sets the value of the protected <code>context</code> field to the specified
     * <code>ScriptContext</code>.
     *
     * @param ctxt The specified <code>ScriptContext</code>.
     * @throws NullPointerException if ctxt is null.
     */
    public function setContext(ScriptContextInterface $ctxt): void
    {
        $this->context = $ctxt;
    }

    /**
     * Returns the value of the protected <code>context</code> field.
     *
     * @return The value of the protected <code>context</code> field.
     */
    public function getContext(): ScriptContextInterface
    {
        return $this->context;
    }

    /**
     * Returns the <code>Bindings</code> with the specified scope value in
     * the protected <code>context</code> field.
     *
     * @param scope The specified scope
     *
     * @return The corresponding <code>Bindings</code>.
     *
     * @throws IllegalArgumentException if the value of scope is
     * invalid for the type the protected <code>context</code> field.
     */
    public function getBindings(int $scope): BindingsInterface
    {
        if ($scope == ScriptContextInterface::GLOBAL_SCOPE) {
            return $this->context->getBindings(ScriptContextInterface::GLOBAL_SCOPE);
        } elseif ($scope == ScriptContextInterface::ENGINE_SCOPE) {
            return $this->context->getBindings(ScriptContextInterface::ENGINE_SCOPE);
        } else {
            throw new \Exception("Invalid scope value.");
        }
    }

    /**
     * Sets the <code>Bindings</code> with the corresponding scope value in the
     * <code>context</code> field.
     *
     * @param bindings The specified <code>Bindings</code>.
     * @param scope The specified scope.
     *
     * @throws IllegalArgumentException if the value of scope is
     * invalid for the type the <code>context</code> field.
     * @throws NullPointerException if the bindings is null and the scope is
     * <code>ScriptContext.ENGINE_SCOPE</code>
     */
    public function setBindings(BindingsInterface $bindings, int $scope): void
    {

        if ($scope == ScriptContextInterface::GLOBAL_SCOPE) {
            $this->context->setBindings($bindings, ScriptContextInterface::GLOBAL_SCOPE);
        } elseif ($scope == ScriptContextInterface::ENGINE_SCOPE) {
            $this->context->setBindings($bindings, ScriptContextInterface::ENGINE_SCOPE);
        } else {
            throw new \Exception("Invalid scope value.");
        }
    }

    /**
     * Sets the specified value with the specified key in the <code>ENGINE_SCOPE</code>
     * <code>Bindings</code> of the protected <code>context</code> field.
     *
     * @param key The specified key.
     * @param value The specified value.
     *
     * @throws NullPointerException if key is null.
     * @throws IllegalArgumentException if key is empty.
     */
    public function put(string $key, $value): void
    {
        $nn = $this->getBindings(ScriptContextInterface::ENGINE_SCOPE);
        if ($nn != null) {
            $nn->put($key, $value);
        }
    }

    /**
     * Gets the value for the specified key in the <code>ENGINE_SCOPE</code> of the
     * protected <code>context</code> field.
     *
     * @return The value for the specified key.
     *
     * @throws NullPointerException if key is null.
     * @throws IllegalArgumentException if key is empty.
     */
    public function get(string $key)
    {
        $nn = $this->getBindings(ScriptContextInterface::ENGINE_SCOPE);
        if ($nn != null) {
            return $nn->get($key);
        }

        return null;
    }

    abstract public function eval(string $script, ?ScriptContextInterface $context = null, ?BindingsInterface $bindings = null);

    /**
     * Returns a <code>SimpleScriptContext</code>.  The <code>SimpleScriptContext</code>:
     *<br><br>
     * <ul>
     * <li>Uses the specified <code>Bindings</code> for its <code>ENGINE_SCOPE</code>
     * </li>
     * <li>Uses the <code>Bindings</code> returned by the abstract <code>getGlobalScope</code>
     * method as its <code>GLOBAL_SCOPE</code>
     * </li>
     * <li>Uses the Reader and Writer in the default <code>ScriptContext</code> of this
     * <code>ScriptEngine</code>
     * </li>
     * </ul>
     * <br><br>
     * A <code>SimpleScriptContext</code> returned by this method is used to implement eval methods
     * using the abstract <code>eval(Reader,Bindings)</code> and <code>eval(String,Bindings)</code>
     * versions.
     *
     * @param nn Bindings to use for the <code>ENGINE_SCOPE</code>
     * @return The <code>SimpleScriptContext</code>
     */
    protected function getScriptContext(BindingsInterface $nn): ScriptContextInterface
    {
        $ctxt = new SimpleScriptContext();
        $gs = $this->getBindings(ScriptContextInterface::GLOBAL_SCOPE);

        if ($gs != null) {
            $ctxt->setBindings($gs, ScriptContextInterface::GLOBAL_SCOPE);
        }

        if ($nn != null) {
            $ctxt->setBindings($nn, ScriptContextInterface::ENGINE_SCOPE);
        } else {
            throw new \Exception("Engine scope Bindings may not be null.");
        }
        return $ctxt;
    }
}
