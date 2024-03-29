<?php

namespace Jabe\Impl\Scripting;

use Script\{
    BindingsInterface,
    CompilableInterface,
    CompiledScript,
    ScriptEngineInterface,
    ScriptException
};
use Jabe\{
    ScriptCompilationException,
    ScriptEvaluationException
};
use Jabe\Delegate\{
    BpmnError,
    VariableScopeInterface
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;

class SourceExecutableScript extends CompiledExecutableScript
{
    //private final static ScriptLogger LOG = ProcessEngineLogger.SCRIPT_LOGGER;

    /** The source of the script. */
    protected $scriptSource;

    /** Flag to signal if the script should be compiled */
    protected bool $shouldBeCompiled = true;

    public function __construct(?string $language, ?string $source)
    {
        parent::__construct($language);
        $this->scriptSource = $source;
    }

    public function execute(ScriptEngineInterface $engine, VariableScopeInterface $variableScope, BindingsInterface $bindings)
    {
        if ($this->shouldBeCompiled) {
            $this->compileScript($engine);
        }

        if ($this->getCompiledScript() !== null) {
            return parent::evaluate($engine, $variableScope, $bindings);
        } else {
            try {
                return $this->evaluateScript($engine, $bindings);
            } catch (ScriptException $e) {
                /*if (e.getCause() instanceof BpmnError) {
                    throw (BpmnError) e.getCause();
                }*/
                $activityIdMessage = $this->getActivityIdExceptionMessage($variableScope);
                throw new ScriptEvaluationException("Unable to evaluate script" . $activityIdMessage);
            }
        }
    }

    protected function compileScript(ScriptEngineInterface $engine): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        if ($processEngineConfiguration->isEnableScriptEngineCaching() && $processEngineConfiguration->isEnableScriptCompilation()) {
            if ($this->getCompiledScript() === null && $this->shouldBeCompiled) {
                // try to compile script
                $compiledScript = $this->compile($engine, $this->language, $this->scriptSource);

                // either the script was successfully compiled or it can't be
                // compiled but we won't try it again
                $this->shouldBeCompiled = false;
            }
        } else {
            // if script compilation is disabled abort
            $this->shouldBeCompiled = false;
        }
    }

    public function compile(ScriptEngineInterface $scriptEngine, ?string $language, ?string $src): CompiledScript
    {
        if ($scriptEngine instanceof CompilableInterface) {
            $compilingEngine = $scriptEngine;

            try {
                $compiledScript = $compilingEngine->compile($src);

                //LOG.debugCompiledScriptUsing(language);

                return $compiledScript;
            } catch (ScriptException $e) {
                throw new ScriptCompilationException("Unable to compile script");
            }
        } else {
            // engine does not support compilation
            return null;
        }
    }

    protected function evaluateScript(ScriptEngineInterface $engine, BindingsInterface $bindings)
    {
        //LOG.debugEvaluatingNonCompiledScript(scriptSource);
        return $engine->eval($this->scriptSource, $bindings);
    }

    public function getScriptSource(): ?string
    {
        return $this->scriptSource;
    }

    /**
     * Sets the script source code. And invalidates any cached compilation result.
     *
     * @param scriptSource
     *          the new script source code
     */
    public function setScriptSource(?string $scriptSource): void
    {
        $this->compiledScript = null;
        $this->shouldBeCompiled = true;
        $this->scriptSource = $scriptSource;
    }

    public function isShouldBeCompiled(): bool
    {
        return $this->shouldBeCompiled;
    }
}
