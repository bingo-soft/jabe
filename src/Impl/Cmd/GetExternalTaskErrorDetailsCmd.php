<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetExternalTaskErrorDetailsCmd implements CommandInterface, \Serializable
{
    private $externalTaskId;

    public function __construct(?string $externalTaskId)
    {
        $this->externalTaskId = $externalTaskId;
    }

    public function serialize()
    {
        return json_encode([
            'externalTaskId' => $this->externalTaskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->externalTaskId = $json->externalTaskId;
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
