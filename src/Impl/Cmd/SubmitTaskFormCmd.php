<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\ExecutionVariableSnapshotObserver;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Task\DelegationState;
use Jabe\Variable\Variables;

class SubmitTaskFormCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $properties = [];

    // only fetch variables if they are actually requested;
    // this avoids unnecessary loading of variables
    protected $returnVariables;
    protected $deserializeValues;

    public function __construct(?string $taskId, array $properties, bool $returnVariables, bool $deserializeValues)
    {
        $this->taskId = $taskId;
        $this->properties = Variables::fromMap($properties);
        $this->returnVariables = $returnVariables;
        $this->deserializeValues = $deserializeValues;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'properties' => serialize($this->properties),
            'returnVariables' => $this->returnVariables,
            'deserializeValues' => $this->deserializeValues
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->properties = unserialize($json->properties);
        $this->returnVariables = $json->returnVariables;
        $this->deserializeValues = $json->deserializeValues;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskWork($task);
        }

        $taskDefinition = $task->getTaskDefinition();
        if ($taskDefinition !== null) {
            $taskFormHandler = $taskDefinition->getTaskFormHandler();
            $taskFormHandler->submitFormVariables($this->properties, $task);
        } else {
            // set variables on standalone task
            $task->setVariables($this->properties);
        }

        $execution = $task->getProcessInstance();
        $variablesListener = null;
        if ($this->returnVariables && $execution !== null) {
            $variablesListener = new ExecutionVariableSnapshotObserver($execution, false, $this->deserializeValues);
        }

        // complete or resolve the task
        if (DelegationState::PENDING == $task->getDelegationState()) {
            $task->resolve();
            $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_RESOLVE);
            $task->triggerUpdateEvent();
        } else {
            $task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_COMPLETE);
            $task->complete();
        }

        if ($this->returnVariables) {
            if ($variablesListener !== null) {
                return $variablesListener->getVariables();
            } else {
                //return $task->getCaseDefinitionId() === null ? null : task.getVariablesTyped(false);
                return $task->getVariablesTyped(false);
            }
        } else {
            return null;
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
