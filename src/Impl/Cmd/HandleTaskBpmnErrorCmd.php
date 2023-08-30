<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class HandleTaskBpmnErrorCmd implements CommandInterface
{
    protected $taskId;
    protected $errorCode;
    protected $errorMessage;
    protected $variables = [];

    public function __construct(?string $taskId, ?string $errorCode, ?string $errorMessage = null, array $variables = [])
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

    public function execute(CommandContext $commandContext, ...$args)
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

    public function isRetryable(): bool
    {
        return false;
    }
}
