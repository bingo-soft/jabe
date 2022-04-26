<?php

namespace Jabe\Engine\Impl\Scripting;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Variable\Mapping\IoParameter;
use Jabe\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Engine\Impl\Delegate\ScriptInvocation;

class ScriptValueProvider implements ParameterValueProviderInterface
{
    protected $script;

    public function __construct(ExecutableScript $script)
    {
        $this->script = $script;
    }

    public function getValue(VariableScopeInterface $variableScope)
    {
        $invocation = new ScriptInvocation($script, $variableScope);
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
