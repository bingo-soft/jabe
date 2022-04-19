<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class GetTaskFormCmd implements CommandInterface, \Serializable
{
    protected $taskId;

    public function __construct(string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
    }

    public function execute(CommandContext $commandContext)
    {
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("No task found for taskId '" . $this->taskId . "'", "task", $task);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTaskVariable($task);
        }

        if ($task->getTaskDefinition() != null) {
            $taskFormHandler = $task->getTaskDefinition()->getTaskFormHandler();
            EnsureUtil::ensureNotNull("No taskFormHandler specified for task '" . $this->taskId . "'", "taskFormHandler", $taskFormHandler);

            return $taskFormHandler->createTaskForm($task);
        } else {
            // Standalone task, no TaskFormData available
            return null;
        }
    }
}
