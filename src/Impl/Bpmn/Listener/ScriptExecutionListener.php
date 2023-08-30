<?php

namespace Jabe\Impl\Bpmn\Listener;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Delegate\ScriptInvocation;
use Jabe\Impl\Scripting\ExecutableScript;

class ScriptExecutionListener implements ExecutionListenerInterface
{
    protected $script;

    public function __construct(ExecutableScript $script)
    {
        $this->script = $script;
    }

    public function notify(DelegateExecutionInterface $execution): void
    {
        $invocation = new ScriptInvocation($this->script, $execution);
        Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation($invocation);
    }

    public function getScript(): ExecutableScript
    {
        return $this->script;
    }
}
