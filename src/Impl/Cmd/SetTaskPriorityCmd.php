<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\TaskEntity;
use Jabe\Impl\Util\EnsureUtil;

class SetTaskPriorityCmd implements CommandInterface
{
    protected $priority;
    protected $taskId;

    public function __construct(?string $taskId, int $priority)
    {
        $this->taskId = $taskId;
        $this->priority = $priority;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId,
            'priority' => $this->priority
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
        $this->priority = $data['priority'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);

        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkTaskPriority($task, $commandContext);

        $task->setPriority($this->priority);

        $task->triggerUpdateEvent();
        $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_SET_PRIORITY);

        return null;
    }

    protected function checkTaskPriority(TaskEntity $task, CommandContext $commandContext)
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
