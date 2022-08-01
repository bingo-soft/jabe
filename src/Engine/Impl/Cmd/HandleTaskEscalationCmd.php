<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class HandleTaskEscalationCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $escalationCode;
    protected $variables = [];

    public function __construct(string $taskId, string $escalationCode, array $variables = [])
    {
        $this->taskId = $taskId;
        $this->escalationCode = $escalationCode;
        $this->variables = $variables;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'escalationCode' => $this->escalationCode,
            'variables' => $this->variables
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->escalationCode = $json->escalationCode;
        $this->variables = $json->variables;
    }

    protected function validateInput(): void
    {
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "taskId", $this->taskId);
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "escalationCode", escalationCode);
    }

    public function execute(CommandContext $commandContext)
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
}
