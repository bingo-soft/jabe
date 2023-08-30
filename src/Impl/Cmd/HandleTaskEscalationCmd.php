<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class HandleTaskEscalationCmd implements CommandInterface
{
    protected $taskId;
    protected $escalationCode;
    protected $variables = [];

    public function __construct(?string $taskId, ?string $escalationCode, array $variables = [])
    {
        $this->taskId = $taskId;
        $this->escalationCode = $escalationCode;
        $this->variables = $variables;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId,
            'escalationCode' => $this->escalationCode,
            'variables' => $this->variables
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
        $this->escalationCode = $data['escalationCode'];
        $this->variables = $data['variables'];
    }

    protected function validateInput(): void
    {
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "taskId", $this->taskId);
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "escalationCode", escalationCode);
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $this->validateInput();

        $task = $commandContext->getTaskManager()->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskWork($task);
        }

        $task->escalation($this->escalationCode, $this->variables);

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
