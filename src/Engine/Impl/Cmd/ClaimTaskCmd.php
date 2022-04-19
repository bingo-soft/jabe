<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\TaskAlreadyClaimedException;
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    TaskEntity,
    TaskManager
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class ClaimTaskCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $userId;

    public function __construct(string $taskId, string $userId)
    {
        $this->taskId = $taskId;
        $this->userId = $userId;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId,
            'taskId' => $this->taskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
        $this->taskId = $json->taskId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkClaimTask($task, $commandContext);

        if ($this->userId != null) {
            if ($task->getAssignee() != null) {
                if ($task->getAssignee() != $this->userId) {
                    // When the task is already claimed by another user, throw exception. Otherwise, ignore
                    // this, post-conditions of method already met.
                    throw new TaskAlreadyClaimedException($task->getId(), $task->getAssignee());
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
}
