<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Listener;

use BpmPlatform\Engine\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Delegate\ScriptInvocation;
use BpmPlatform\Engine\Impl\Scripting\ExecutableScript;

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
