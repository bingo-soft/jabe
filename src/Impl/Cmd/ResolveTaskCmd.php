<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Persistence\Entity\TaskEntity;

class ResolveTaskCmd extends CompleteTaskCmd
{
    public function __construct(string $taskId, array $variables)
    {
        parent::__construct($taskId, $variables, false, false);
    }

    protected function completeTask(TaskEntity $task): void
    {
        $task->resolve();
        $task->triggerUpdateEvent();
        $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_RESOLVE);
    }
}
