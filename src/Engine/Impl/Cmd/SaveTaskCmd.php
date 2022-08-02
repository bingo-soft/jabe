<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Exception\{
    NotAllowedException,
    NotValidException,
    NullValueException
};
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    TaskEntity,
    TaskState
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Management\Metrics;
use Jabe\Engine\Task\TaskInterface;

class SaveTaskCmd implements CommandInterface, \Serializable
{
    protected $task;

    public function __construct(TaskInterface $task)
    {
        $this->task = $task;
    }

    public function serialize()
    {
        return json_encode([
            'task' => serialize($this->task)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->task = unserialize($json->task);
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("task", "task", $this->task);
        $this->validateStandaloneTask($this->task, $commandContext);

        $operation = null;
        if ($this->task->getRevision() == 0) {
            try {
                $this->checkCreateTask($this->task, $commandContext);
                $this->task->ensureParentTaskActive();
                $this->task->propagateParentTaskTenantId();
                $this->task->insert();
                $operation = UserOperationLogEntryInterface::OPERATION_TYPE_CREATE;
                $this->task->executeMetrics(Metrics::ACTIVTY_INSTANCE_START, $commandContext);
            } catch (\Exception $e) {
                throw new NotValidException($e->getMessage(), $e);
            }

            $this->task->fireAuthorizationProvider();
            $this->task->transitionTo(TaskState::STATE_CREATED);
        } else {
            $this->checkTaskAssign($this->task, $commandContext);
            $this->task->update();
            $operation = UserOperationLogEntryInterface::OPERATION_TYPE_UPDATE;
            $this->task->fireAuthorizationProvider();
            $this->task->triggerUpdateEvent();
        }

        $this->task->executeMetrics(Metrics::UNIQUE_TASK_WORKERS, $commandContext);
        $this->task->logUserOperation($operation);

        return null;
    }

    protected function validateStandaloneTask(TaskEntity $task, CommandContext $commandContext): void
    {
        $standaloneTasksEnabled = $commandContext->getProcessEngineConfiguration()->isStandaloneTasksEnabled();
        if (!$standaloneTasksEnabled && $task->isStandaloneTask()) {
            throw new NotAllowedException("Cannot save standalone task They are disabled in the process engine configuration.");
        }
    }

    protected function checkTaskAssign(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskAssign($task);
        }
    }

    protected function checkCreateTask(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateTask($task);
        }
    }
}
