<?php

namespace Jabe\Impl\Cmd;

use Jabe\TaskAlreadyClaimedException;
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    TaskEntity,
    TaskManager
};
use Jabe\Impl\Util\EnsureUtil;

class ClaimTaskCmd implements CommandInterface
{
    protected $taskId;
    protected $userId;

    public function __construct(?string $taskId, ?string $userId)
    {
        $this->taskId = $taskId;
        $this->userId = $userId;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId,
            'taskId' => $this->taskId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
        $this->taskId = $data['taskId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkClaimTask($task, $commandContext);

        if ($this->userId !== null) {
            if ($task->getAssignee() !== null) {
                if ($task->getAssignee() != $this->userId) {
                    // When the task is already claimed by another user, throw exception. Otherwise, ignore
                    // this, post-conditions of method already met.
                    throw new TaskAlreadyClaimedException(sprintf("Task already claimed, task id: %s, task assignee: %s", $task->getId(), $task->getAssignee()));
                }
            } else {
                $task->setAssignee($this->userId);
            }
        } else {
            // Task should be assigned to no one
            $task->setAssignee(null);
        }
        $task->triggerUpdateEvent();
        $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_CLAIM);

        return null;
    }

    protected function checkClaimTask(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskWork($task);
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
