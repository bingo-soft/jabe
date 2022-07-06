<?php

namespace Jabe\Engine\Impl\Util\Scripting;

interface ScriptEngineInterface
{
    /**
     * Reserved key for a named value that passes
     * an array of positional arguments to a script.
     */
    public const ARGV = "script.argv";

    /**
     * Reserved key for a named value that is
     * the name of the file being executed.
     */
    public const FILENAME = "script.filename";

    /**
     * Reserved key for a named value that is
     * the name of the <code>ScriptEngine</code> implementation.
     */
    public const ENGINE = "script.engine";

    /**
     * Reserved key for a named value that identifies
     * the version of the <code>ScriptEngine</code> implementation.
     */
    public const ENGINE_VERSION = "script.engine_version";

    /**
     * Reserved key for a named value that identifies
     * the short name of the scripting language.  The name is used by the
     * <code>ScriptEngineManager</code> to locate a <code>ScriptEngine</code>
     * with a given name in the <code>getEngineByName</code> method.
     */
    public const NAME = "script.name";

    /**
     * Reserved key for a named value that is
     * the full name of Scripting Language supported by the implementation.
     */
    public const LANGUAGE = "script.language";

    /**
     * Reserved key for the named value that identifies
     * the version of the scripting language supported by the implementation.
     */
    public const LANGUAGE_VERSION = "script.language_version";

    /**
     * Causes the immediate execution of the script whose source is the String
     * passed as the first argument.  The script may be reparsed or recompiled before
     * execution.  State left in the engine from previous executions, including
     * variable values and compiled procedures may be visible during this execution.
     *
     * @param script The script to be executed by the script engine.
     *
     * @param context A <code>ScriptContext</code> exposing sets of attributes in
     * different scopes.  The meanings of the scopes <code>ScriptContext.GLOBAL_SCOPE</code>,
     * and <code>ScriptContext.ENGINE_SCOPE</code> are defined in the specification.
     * <br><br>
     * The <code>ENGINE_SCOPE</code> <code>Bindings</code> of the <code>ScriptContext</code> contains the
     * bindings of scripting variables to application objects to be used during this
     * script execution.
     *
     *
     * @return mixed The value returned from the execution of the script.
     *
     * @throws ScriptException if an error occurs in script. ScriptEngines should create and throw
     * <code>ScriptException</code> wrappers for checked Exceptions thrown by underlying scripting
     * implementations.
     * @throws NullPointerException if either argument is null.
     */
    public function eval(string $script, ?ScriptContextInterface $context = null, ?BindingsInterface $bindings = null);

    /**
     * Sets a key/value pair in the state of the ScriptEngine that may either create
     * a Java Language Binding to be used in the execution of scripts or be used in some
     * other way, depending on whether the key is reserved.  Must have the same effect as
     * <code>getBindings(ScriptContext.ENGINE_SCOPE).put</code>.
     *
     * @param key The name of named value to add
     * @param value The value of named value to add.
     *
     * @throws NullPointerException if key is null.
     * @throws IllegalArgumentException if key is empty.
     */
    public function put(string $key, $value);

    /**
     * Retrieves a value set in the state of this engine.  The value might be one
     * which was set using <code>setValue</code> or some other value in the state
     * of the <code>ScriptEngine</code>, depending on the implementation.  Must have the same effect
     * as <code>getBindings(ScriptContext.ENGINE_SCOPE).get</code>
     *
     * @param key The key whose value is to be returned
     * @return mixed the value for the given key
     *
     * @throws NullPointerException if key is null.
     * @throws IllegalArgumentException if key is empty.
     */
    public function get(string $key);

    /**
     * Returns a scope of named values.  The possible scopes are:
     * <br><br>
     * <ul>
     * <li><code>ScriptContext.GLOBAL_SCOPE</code> - The set of named values representing global
     * scope. If this <code>ScriptEngine</code> is created by a <code>ScriptEngineManager</code>,
     * then the manager sets global scope bindings. This may be <code>null</code> if no global
     * scope is associated with this <code>ScriptEngine</code></li>
     * <li><code>ScriptContext.ENGINE_SCOPE</code> - The set of named values representing the state of
     * this <code>ScriptEngine</code>.  The values are generally visible in scripts using
     * the associated keys as variable names.</li>
     * <li>Any other value of scope defined in the default <code>ScriptContext</code> of the <code>ScriptEngine</code>.
     * </li>
     * </ul>
     * <br><br>
     * The <code>Bindings</code> instances that are returned must be identical to those returned by the
     * <code>getBindings</code> method of <code>ScriptContext</code> called with corresponding arguments on
     * the default <code>ScriptContext</code> of the <code>ScriptEngine</code>.
     *
     * @param scope Either <code>ScriptContext.ENGINE_SCOPE</code> or <code>ScriptContext.GLOBAL_SCOPE</code>
     * which specifies the <code>Bindings</code> to return.  Implementations of <code>ScriptContext</code>
     * may define additional scopes.  If the default <code>ScriptContext</code> of the <code>ScriptEngine</code>
     * defines additional scopes, any of them can be passed to get the corresponding <code>Bindings</code>.
     *
     * @return BindingsInterface The <code>Bindings</code> with the specified scope.
     *
     * @throws IllegalArgumentException if specified scope is invalid
     *
     */
    public function getBindings(int $scope): BindingsInterface;

    /**
     * Sets a scope of named values to be used by scripts.  The possible scopes are:
     *<br><br>
     * <ul>
     * <li><code>ScriptContext.ENGINE_SCOPE</code> - The specified <code>Bindings</code> replaces the
     * engine scope of the <code>ScriptEngine</code>.
     * </li>
     * <li><code>ScriptContext.GLOBAL_SCOPE</code> - The specified <code>Bindings</code> must be visible
     * as the <code>GLOBAL_SCOPE</code>.
     * </li>
     * <li>Any other value of scope defined in the default <code>ScriptContext</code> of the <code>ScriptEngine</code>.
     *</li>
     * </ul>
     * <br><br>
     * The method must have the same effect as calling the <code>setBindings</code> method of
     * <code>ScriptContext</code> with the corresponding value of <code>scope</code> on the default
     * <code>ScriptContext</code> of the <code>ScriptEngine</code>.
     *
     * @param bindings The <code>Bindings</code> for the specified scope.
     * @param scope The specified scope.  Either <code>ScriptContext.ENGINE_SCOPE</code>,
     * <code>ScriptContext.GLOBAL_SCOPE</code>, or any other valid value of scope.
     *
     * @throws IllegalArgumentException if the scope is invalid
     * @throws NullPointerException if the bindings is null and the scope is
     * <code>ScriptContext.ENGINE_SCOPE</code>
     */
    public function setBindings(BindingsInterface $bindings, int $scope): void;

    /**
     * Returns an uninitialized <code>Bindings</code>.
     *
     * @return A <code>Bindings</code> that can be used to replace the state of this <code>ScriptEngine</code>.
     **/
    public function createBindings(): BindingsInterface;


    /**
     * Returns the default <code>ScriptContext</code> of the <code>ScriptEngine</code> whose Bindings, Reader
     * and Writers are used for script executions when no <code>ScriptContext</code> is specified.
     *
     * @return ScriptContextInterface The default <code>ScriptContext</code> of the <code>ScriptEngine</code>.
     */
    public function getContext(): ScriptContextInterface;

    /**
     * Sets the default <code>ScriptContext</code> of the <code>ScriptEngine</code> whose Bindings, Reader
     * and Writers are used for script executions when no <code>ScriptContext</code> is specified.
     *
     * @param context A <code>ScriptContext</code> that will replace the default <code>ScriptContext</code> in
     * the <code>ScriptEngine</code>.
     * @throws NullPointerException if context is null.
     */
    public function setContext(ScriptContextInterface $context): void;

    /**
     * Returns a <code>ScriptEngineFactory</code> for the class to which this <code>ScriptEngine</code> belongs.
     *
     * @return ScriptEngineFactoryInterface The <code>ScriptEngineFactory</code>
     */
    public function getFactory(): ScriptEngineFactoryInterface;
}
