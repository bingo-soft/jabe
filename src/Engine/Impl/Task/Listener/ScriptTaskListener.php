<?php

namespace BpmPlatform\Engine\Impl\Task\Listener;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Delegate\{
    DelegateTaskInterface,
    TaskListenerInterface
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Delegate\ScriptInvocation;
use BpmPlatform\Engine\Impl\Scripting\ExecutableScript;

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
