<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\TaskEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

class SetTaskPriorityCmd implements CommandInterface, \Serializable
{
    protected $priority;
    protected $taskId;

    public function __construct(string $taskId, int $priority)
    {
        $this->taskId = $taskId;
        $this->priority = $priority;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'priority' => $this->priority
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->priority = $json->priority;
    }

    public function execute(CommandContext $commandContext)
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
}
