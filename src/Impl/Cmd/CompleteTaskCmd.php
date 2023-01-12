<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExecutionVariableSnapshotObserver,
    TaskEntity,
    TaskManager
};
use Jabe\Impl\Util\EnsureUtil;

class CompleteTaskCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $variables = [];

    // only fetch variables if they are actually requested;
    // this avoids unnecessary loading of variables
    protected $returnVariables;
    protected $deserializeReturnedVariables;

    public function __construct(
        ?string $taskId,
        array $variables,
        bool $returnVariables = false,
        bool $deserializeReturnedVariables = false
    ) {
        $this->taskId = $taskId;
        $this->variables = $variables;
        $this->returnVariables = $returnVariables;
        $this->deserializeReturnedVariables = $deserializeReturnedVariables;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'variables' => $this->variables,
            'returnVariables' => $this->returnVariables,
            'deserializeReturnedVariables' => $this->deserializeReturnedVariables,
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->variables = $json->variables;
        $this->returnVariables = $json->returnVariables;
        $this->deserializeReturnedVariables = $json->deserializeReturnedVariables;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);

        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkCompleteTask($task, $commandContext);

        if (!empty($this->variables)) {
            $task->setExecutionVariables($this->variables);
        }

        $execution = $task->getProcessInstance();
        $variablesListener = null;

        if ($this->returnVariables && $execution !== null) {
            $variablesListener = new ExecutionVariableSnapshotObserver($execution, false, $this->deserializeReturnedVariables);
        }

        $this->completeTask($task);

        if ($this->returnVariables) {
            if ($variablesListener !== null) {
                return $variablesListener->getVariables();
            } else {
                //return $task->getCaseDefinitionId() !== null ? null : $task->getVariablesTyped(false);
                return $task->getVariablesTyped(false);
            }
        } else {
            return [];
        }
    }

    protected function completeTask(TaskEntity $task): void
    {
        $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_COMPLETE);
        $task->complete();
    }

    protected function checkCompleteTask(TaskEntity $task, CommandContext $commandContext): void
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
