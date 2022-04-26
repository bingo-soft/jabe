<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    PropertyChange,
    TaskEntity,
    VariableInstanceEntity
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class SetTaskVariablesCmd extends AbstractSetVariableCmd implements VariableInstanceLifecycleListenerInterface
{

    protected $taskLocalVariablesUpdated = false;

    public function __construct(string $taskId, array $variables, bool $isLocal)
    {
        parent::__construct($taskId, $variables, $isLocal);
    }

    protected function getEntity(): TaskEntity
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->entityId);

        $task =  $commandContext
            ->getTaskManager()
            ->findTaskById($this->entityId);

        EnsureUtil::ensureNotNull("task " . $this->entityId . " doesn't exist", "task", $task);

        $this->checkSetTaskVariables($task);

        $task->addCustomLifecycleListener($this);

        return $task;
    }

    protected function onSuccess(AbstractVariableScope $scope): void
    {
        $task = $scope;

        if ($this->taskLocalVariablesUpdated) {
            $task->triggerUpdateEvent();
        }

        $task->removeCustomLifecycleListener($this);

        parent::onSuccess($scope);
    }

    protected function getContextExecution(): ExecutionEntity
    {
        return $this->getEntity()->getExecution();
    }

    protected function logVariableOperation(AbstractVariableScope $scope): void
    {
        $task = $scope;
        $commandContext->getOperationLogManager()->logVariableOperation(
            $this->getLogEntryOperation(),
            null,
            $task->getId(),
            PropertyChange::emptyChange()
        );
    }

    protected function checkSetTaskVariables(TaskEntity $task): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateTaskVariable($task);
        }
    }

    protected function onLocalVariableChanged(): void
    {
        $this->taskLocalVariablesUpdated = true;
    }

    public function onCreate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $this->onLocalVariableChanged();
    }

    public function onDelete(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $this->onLocalVariableChanged();
    }

    public function onUpdate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $this->onLocalVariableChanged();
    }
}
