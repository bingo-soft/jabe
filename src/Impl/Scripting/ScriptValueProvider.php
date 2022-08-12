<?php

namespace Jabe\Impl\Scripting;

use Jabe\ProcessEngineException;
use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Impl\Delegate\ScriptInvocation;

class ScriptValueProvider implements ParameterValueProviderInterface
{
    protected $script;

    public function __construct(ExecutableScript $script)
    {
        $this->script = $script;
    }

    public function getValue(VariableScopeInterface $variableScope)
    {
        $invocation = new ScriptInvocation($this->script, $variableScope);
        try {
            Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation($invocation);
        } catch (\Exception $e) {
            throw new ProcessEngineException($e->getMessage());
        }
        return $invocation->getInvocationResult();
    }

    public function isDynamic(): bool
    {
        return true;
    }

    public function getScript(): ExecutableScript
    {
        return $this->script;
    }

    public function setScript(ExecutableScript $script): void
    {
        $this->script = $script;
    }
}
