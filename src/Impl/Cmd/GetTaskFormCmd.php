<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetTaskFormCmd implements CommandInterface
{
    protected $taskId;

    public function __construct(?string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("No task found for taskId '" . $this->taskId . "'", "task", $task);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTaskVariable($task);
        }

        if ($task->getTaskDefinition() !== null) {
            $taskFormHandler = $task->getTaskDefinition()->getTaskFormHandler();
            EnsureUtil::ensureNotNull("No taskFormHandler specified for task '" . $this->taskId . "'", "taskFormHandler", $taskFormHandler);

            return $taskFormHandler->createTaskForm($task);
        } else {
            // Standalone task, no TaskFormData available
            return null;
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
