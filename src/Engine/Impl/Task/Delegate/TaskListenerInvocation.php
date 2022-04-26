<?php

namespace Jabe\Engine\Impl\Task\Delegate;

use Jabe\Engine\Delegate\{
    BaseDelegateExecutionInterface,
    DelegateTaskInterface,
    TaskListenerInterface
};
use Jabe\Engine\Impl\Delegate\DelegateInvocation;

class TaskListenerInvocation extends DelegateInvocation
{
    protected $taskListenerInstance;
    protected $delegateTask;

    public function __construct(TaskListenerInterface $taskListenerInstance, DelegateTaskInterface $delegateTask, ?BaseDelegateExecutionInterface $contextExecution = null)
    {
        parent::__construct($contextExecution, null);
        $this->taskListenerInstance = $taskListenerInstance;
        $this->delegateTask = $delegateTask;
    }

    protected function invoke(): void
    {
        $this->taskListenerInstance->notify($this->delegateTask);
    }
}
