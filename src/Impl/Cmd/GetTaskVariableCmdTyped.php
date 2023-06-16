<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\TaskEntity;
use Jabe\Impl\Util\EnsureUtil;

class GetTaskVariableCmdTyped implements CommandInterface
{
    protected $taskId;
    protected $variableName;
    protected $isLocal;
    protected $deserializeValue;

    public function __construct(?string $taskId, ?string $variableName, bool $isLocal, bool $deserializeValue)
    {
        $this->taskId = $taskId;
        $this->variableName = $variableName;
        $this->isLocal = $isLocal;
        $this->deserializeValue = $deserializeValue;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId,
            'variableName' => $this->variableName,
            'isLocal' => $this->isLocal,
            'deserializeValue' => $this->deserializeValue
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
        $this->variableName = $data['variableName'];
        $this->isLocal = $data['isLocal'];
        $this->deserializeValue = $data['deserializeValue'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);
        EnsureUtil::ensureNotNull("variableName", "variableName", $this->variableName);

        $task = Context::getCommandContext()
            ->getTaskManager()
            ->findTaskById($this->taskId);

        EnsureUtil::ensureNotNull("task " . $this->taskId . " doesn't exist", "task", $task);

        $this->checkGetTaskVariableTyped($task, $commandContext);

        $value = null;

        if ($this->isLocal) {
            $value = $task->getVariableLocalTyped($this->variableName, $this->deserializeValue);
        } else {
            $value = $task->getVariableTyped($this->variableName, $this->deserializeValue);
        }

        return $value;
    }

    protected function checkGetTaskVariableTyped(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTaskVariable($task);
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
