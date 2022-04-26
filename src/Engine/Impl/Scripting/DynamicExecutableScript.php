<?php

namespace Jabe\Engine\Impl\Scripting;

use Jabe\Engine\Impl\Util\Scripting\{
    BindingsInterface,
    ScriptEngineInterface,
    ScriptException
};
use Jabe\Engine\ScriptEvaluationException;
use Jabe\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};

abstract class DynamicExecutableScript extends ExecutableScript
{
    protected $scriptExpression;

    public function __construct(ExpressionInterface $scriptExpression, string $language)
    {
        parent::__construct($language);
        $this->scriptExpression = $scriptExpression;
    }

    public function evaluate(ScriptEngineInterface $scriptEngine, VariableScopeInterface $variableScope, BindingsInterface $bindings)
    {
        $source = $this->getScriptSource($variableScope);
        try {
            return $scriptEngine->eval($source, $bindings);
        } catch (ScriptException $e) {
            $activityIdMessage = $this->getActivityIdExceptionMessage($variableScope);
            throw new ScriptEvaluationException("Unable to evaluate script" . $activityIdMessage);
        }
    }

    protected function evaluateExpression(VariableScopeInterface $variableScope): string
    {
        return $this->scriptExpression->getValue($variableScope);
    }

    abstract public function getScriptSource(VariableScopeInterface $variableScope): string;
}
