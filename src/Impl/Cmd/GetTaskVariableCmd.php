<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\TaskEntity;
use Jabe\Impl\Util\EnsureUtil;

class GetTaskVariableCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $variableName;
    protected $isLocal;

    public function __construct(?string $taskId, ?string $variableName, bool $isLocal)
    {
        $this->taskId = $taskId;
        $this->variableName = $variableName;
        $this->isLocal = $isLocal;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'variableName' => $this->variableName,
            'isLocal' => $this->isLocal
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->variableName = $json->variableName;
        $this->isLocal = $json->isLocal;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);
        EnsureUtil::ensureNotNull("variableName", "variableName", $this->variableName);

        $task = Context::getCommandContext()
            ->getTaskManager()
            ->findTaskById($this->taskId);

            EnsureUtil::ensureNotNull("task " . $this->taskId . " doesn't exist", "task", $task);

        $this->checkGetTaskVariable($task, $commandContext);

        $value = null;

        if ($this->isLocal) {
            $value = $task->getVariableLocal($this->variableName);
        } else {
            $value = $task->getVariable($this->variableName);
        }

        return $value;
    }

    protected function checkGetTaskVariable(TaskEntity $task, CommandContext $commandContext)
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTaskVariable($task);
        }
    }
}
