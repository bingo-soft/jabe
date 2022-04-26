<?php

namespace Jabe\Engine\Impl\Util\Scripting;

abstract class CompiledScript
{
    /**
     * Executes the program stored in this <code>CompiledScript</code> object.
     *
     * @param context A <code>ScriptContext</code> that is used in the same way as
     * the <code>ScriptContext</code> passed to the <code>eval</code> methods of
     * <code>ScriptEngine</code>.
     *
     * @return The value returned by the script execution, if any.  Should return <code>null</code>
     * if no value is returned by the script execution.
     *
     * @throws ScriptException if an error occurs.
     * @throws NullPointerException if context is null.
     */

    abstract public function evalContext(ScriptContextInterface $context);

    /**
     * Executes the program stored in the <code>CompiledScript</code> object using
     * the supplied <code>Bindings</code> of attributes as the <code>ENGINE_SCOPE</code> of the
     * associated <code>ScriptEngine</code> during script execution.  If bindings is null,
     * then the effect of calling this method is same as that of eval(getEngine()->getContext()).
     * <p>.
     * The <code>GLOBAL_SCOPE</code> <code>Bindings</code>, <code>Reader</code> and <code>Writer</code>
     * associated with the default <code>ScriptContext</code> of the associated <code>ScriptEngine</code> are used.
     *
     * @param bindings The bindings of attributes used for the <code>ENGINE_SCOPE</code>.
     *
     * @return The return value from the script execution
     *
     * @throws ScriptException if an error occurs.
     */
    public function eval(?BindingsInterface $bindings = null)
    {
        $ctxt = $this->getEngine()->getContext();

        if ($bindings != null) {
            $tempctxt = new SimpleScriptContext();
            $tempctxt->setBindings($bindings, ScriptContextInterface::ENGINE_SCOPE);
            $tempctxt->setBindings(
                $ctxt->getBindings(ScriptContextInterface::GLOBAL_SCOPE),
                ScriptContextInterface::GLOBAL_SCOPE
            );
            $tempctxt->setWriter($ctxt->getWriter());
            $tempctxt->setReader($ctxt->getReader());
            $tempctxt->setErrorWriter($ctxt->getErrorWriter());
            $ctxt = $tempctxt;
        }

        return $this->evalContext($ctxt);
    }

    /**
     * Returns the <code>ScriptEngine</code> whose <code>compile</code> method created this <code>CompiledScript</code>.
     * The <code>CompiledScript</code> will execute in this engine.
     *
     * @return The <code>ScriptEngine</code> that created this <code>CompiledScript</code>
     */
    abstract public function getEngine(): ScriptEngineInterface;
}
