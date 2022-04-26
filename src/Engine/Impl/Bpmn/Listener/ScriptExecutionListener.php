<?php

namespace Jabe\Engine\Impl\Bpmn\Listener;

use Jabe\Engine\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Delegate\ScriptInvocation;
use Jabe\Engine\Impl\Scripting\ExecutableScript;

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
