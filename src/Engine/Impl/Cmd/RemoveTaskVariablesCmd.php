<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    PropertyChange
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class RemoveTaskVariablesCmd extends AbstractRemoveVariableCmd
{
    public function __construct(string $taskId, array $variableNames, bool $isLocal)
    {
        parent__construct($taskId, $variableNames, $isLocal);
    }

    protected function getEntity(): TaskEntity
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->entityId);

        $task = $commandContext
            ->getTaskManager()
            ->findTaskById($this->entityId);

            EnsureUtil::ensureNotNull("Cannot find task with id " . $this->entityId, "task", $task);

        $this->checkRemoveTaskVariables($task);

        return $task;
    }

    protected function getContextExecution(): ExecutionEntity
    {
        return $this->getEntity()->getExecution();
    }

    protected function logVariableOperation(AbstractVariableScope $scope): void
    {
        $task = $scope;
        $commandContext->getOperationLogManager()->logVariableOperation($this->getLogEntryOperation(), null, $task->getId(), PropertyChange::emptyChange());
    }

    protected function checkRemoveTaskVariables(TaskEntity $task): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateTaskVariable($task);
        }
    }
}
