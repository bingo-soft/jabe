<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetExternalTaskErrorDetailsCmd implements CommandInterface, \Serializable
{
    private $externalTaskId;

    public function __construct(string $externalTaskId)
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

    public function execute(CommandContext $commandContext)
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
}
