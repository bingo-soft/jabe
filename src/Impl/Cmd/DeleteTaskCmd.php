<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    TaskEntity,
    TaskManager
};

class DeleteTaskCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $taskIds = [];
    protected $cascade;
    protected $deleteReason;

    public function __construct($taskIds, ?string $deleteReason, ?bool $cascade = false)
    {
        if (is_array($taskIds)) {
            $this->taskIds = $taskIds;
        } else {
            $this->taskId = $taskIds;
        }
        $this->cascade = $cascade;
        $this->deleteReason = $deleteReason;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'taskIds' => $this->taskIds,
            'cascade' => $this->cascade,
            'deleteReason' => $this->deleteReason
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->taskIds = $json->taskIds;
        $this->cascade = $json->cascade;
        $this->deleteReason = $json->deleteReason;
    }

    public function execute(CommandContext $commandContext)
    {
        if ($this->taskId !== null) {
            $this->deleteTask($this->taskId, $commandContext);
        } elseif (!empty($this->taskIds)) {
            foreach ($this->taskIds as $taskId) {
                $this->deleteTask($taskId, $commandContext);
            }
        } else {
            throw new ProcessEngineException("taskId and taskIds are null");
        }

        return null;
    }

    protected function deleteTask(string $taskId, CommandContext $commandContext): void
    {
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($taskId);

        if ($task !== null) {
            if ($task->getExecutionId() !== null) {
                throw new ProcessEngineException("The task cannot be deleted because is part of a running process");
            }/* elseif (task.getCaseExecutionId() !== null) {
                throw new ProcessEngineException("The task cannot be deleted because is part of a running case instance");
            }*/

            $this->checkDeleteTask($task, $commandContext);
            $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELETE);

            $reason = ($this->deleteReason === null || strlen($this->deleteReason) == 0) ? TaskEntity::DELETE_REASON_DELETED : $this->deleteReason;
            $task->delete($reason, $this->cascade);
        } elseif ($this->cascade) {
            Context::getCommandContext()
            ->getHistoricTaskInstanceManager()
            ->deleteHistoricTaskInstanceById($this->taskId);
        }
    }

    protected function checkDeleteTask(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteTask($task);
        }
    }
}
