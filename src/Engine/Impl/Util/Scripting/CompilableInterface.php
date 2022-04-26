<?php

namespace Jabe\Engine\Impl\Util\Scripting;

interface CompilableInterface
{
    /**
     * Compiles the script (source represented as a <code>String</code>) for
     * later execution.
     *
     * @param script The source of the script, represented as a <code>String</code>.
     *
     * @return An instance of a subclass of <code>CompiledScript</code> to be executed later using one
     * of the <code>eval</code> methods of <code>CompiledScript</code>.
     *
     * @throws ScriptException if compilation fails.
     * @throws NullPointerException if the argument is null.
     *
     */

    public function compile(string $script): CompiledScript;
}
