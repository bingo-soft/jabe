<?php

namespace Jabe\Impl\Cmd;

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

class DelegateTaskCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $userId;

    public function __construct(?string $taskId, ?string $userId)
    {
        $this->taskId = $taskId;
        $this->userId = $userId;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'userId' => $this->userId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->userId = $json->userId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);

        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkDelegateTask($task, $commandContext);

        $task->delegate($this->userId);

        $task->triggerUpdateEvent();
        $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELEGATE);

        return null;
    }

    protected function checkDelegateTask(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskAssign($task);
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
