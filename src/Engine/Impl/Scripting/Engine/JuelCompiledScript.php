<?php

namespace BpmPlatform\Engine\Impl\Scripting\Engine;

use BpmPlatform\Engine\Impl\Util\Scripting\{
    CompiledScript,
    ScriptContextInterface,
    ScriptEngineInterface
};
use BpmPlatform\Engine\Impl\Util\El\ValueExpression;

class JuelCompiledScript extends CompiledScript
{
    private $engine;

    private $valueExpression;

    public function __construct(JuelScriptEngine $engine, ValueExpression $valueExpression)
    {
        $this->engine = $engine;
        $this->valueExpression = $valueExpression;
    }

    public function getEngine(): ScriptEngineInterface
    {
        return $this->engine;
    }

    public function eval(ScriptContextInterface $ctx)
    {
        return $this->engine->evaluateExpression($this->valueExpression, $ctx);
    }
}
