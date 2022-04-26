<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\TaskEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetTaskVariableCmdTyped implements CommandInterface, \Serializable
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

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'variableName' => $this->variableName,
            'isLocal' => $this->isLocal,
            'deserializeValue' => $this->deserializeValue
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->variableName = $json->variableName;
        $this->isLocal = $json->isLocal;
        $this->deserializeValue = $json->deserializeValue;
    }

    public function execute(CommandContext $commandContext)
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
}
