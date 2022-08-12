<?php

namespace Jabe\Impl\Form\Handler;

use Jabe\Impl\Delegate\DelegateInvocation;
use Jabe\Impl\Persistence\Entity\TaskEntity;

class CreateTaskFormInvocation extends DelegateInvocation
{
    protected $taskFormHandler;
    protected $task;

    public function __construct(TaskFormHandlerInterface $taskFormHandler, TaskEntity $task)
    {
        parent::__construct(null, null);
        $this->taskFormHandler = $taskFormHandler;
        $this->task = $task;
    }

    protected function invoke()
    {
        $this->invocationResult = $this->taskFormHandler->createTaskForm($this->task);
    }
}
