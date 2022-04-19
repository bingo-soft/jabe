<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\Exception\NotFoundException;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class HandleTaskBpmnErrorCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $errorCode;
    protected $errorMessage;
    protected $variables = [];

    public function __construct(string $taskId, string $errorCode, string $errorMessage = null, array $variables = [])
    {
        $this->taskId = $taskId;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->variables = $variables;
    }

    protected function validateInput(): void
    {
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "taskId", $this->taskId);
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "errorCode", $this->errorCode);
    }

    public function execute(CommandContext $commandContext)
    {
        $this->validateInput();

        $task = $commandContext->getTaskManager()->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskWork($task);
        }

        $task->bpmnError($this->errorCode, $this->errorMessage, $this->variables);

        return null;
    }
}
