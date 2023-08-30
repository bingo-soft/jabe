<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetExternalTaskErrorDetailsCmd implements CommandInterface
{
    private $externalTaskId;

    public function __construct(?string $externalTaskId)
    {
        $this->externalTaskId = $externalTaskId;
    }

    public function __serialize(): array
    {
        return [
            'externalTaskId' => $this->externalTaskId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->externalTaskId = $data['externalTaskId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("externalTaskId", "externalTaskId", $this->externalTaskId);

        $externalTask = $commandContext
            ->getExternalTaskManager()
            ->findExternalTaskById($this->externalTaskId);

            EnsureUtil::ensureNotNull("No external task found with id " . $this->externalTaskId, "externalTask", $externalTask);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessInstance($externalTask->getProcessInstanceId());
        }

        return $externalTask->getErrorDetails();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
