<?php

namespace Jabe\Impl\Task\Listener;

use Jabe\ProcessEngineException;
use Jabe\Delegate\{
    DelegateTaskInterface,
    TaskListenerInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Delegate\ScriptInvocation;
use Jabe\Impl\Scripting\ExecutableScript;

class ScriptTaskListener implements TaskListenerInterface
{
    protected $script;

    public function __construct(ExecutableScript $script)
    {
        $this->script = $script;
    }

    public function notify(DelegateTaskInterface $delegateTask): void
    {
        $invocation = new ScriptInvocation($this->script, $delegateTask);
        try {
            Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation($invocation);
        } catch (\Exception $e) {
            throw new ProcessEngineException($e->getMessage());
        }
    }

    public function getScript(): ExecutableScript
    {
        return $this->script;
    }
}
