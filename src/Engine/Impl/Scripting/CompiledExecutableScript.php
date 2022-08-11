<?php

namespace Jabe\Engine\Impl\Scripting;

use Script\{
    CompiledScript,
    BindingsInterface,
    ScriptEngineInterface,
    ScriptException
};
use Jabe\Engine\ScriptEvaluationException;
use Jabe\Engine\Delegate\{
    BpmnError,
    VariableScopeInterface
};
use Jabe\Engine\Impl\ProcessEngineLogger;

class CompiledExecutableScript extends ExecutableScript
{
    //private final static ScriptLogger LOG = ProcessEngineLogger.SCRIPT_LOGGER;
    protected $compiledScript;

    public function __construct(string $language, ?CompiledScript $compiledScript = null)
    {
        parent::__construct($language);
        $this->compiledScript = $compiledScript;
    }

    public function getCompiledScript(): CompiledScript
    {
        return $this->compiledScript;
    }

    public function setCompiledScript(CompiledScript $compiledScript): void
    {
        $this->compiledScript = $compiledScript;
    }

    public function evaluate(ScriptEngineInterface $scriptEngine, VariableScopeInterface $variableScope, BindingsInterface $bindings)
    {
        try {
            //LOG.debugEvaluatingCompiledScript(language);
            return $this->getCompiledScript()->eval($bindings);
        } catch (ScriptException $e) {
            /*if (e.getCause() instanceof BpmnError) {
                throw (BpmnError) e.getCause();
            }*/
            $activityIdMessage = $this->getActivityIdExceptionMessage($variableScope);
            throw new ScriptEvaluationException("Unable to evaluate script" . $activityIdMessage . ": ");
        }
    }
}
