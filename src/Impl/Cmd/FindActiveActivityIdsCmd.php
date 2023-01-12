<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExecutionManager
};
use Jabe\Impl\Util\EnsureUtil;

class FindActiveActivityIdsCmd implements CommandInterface, \Serializable
{
    protected $executionId;

    public function __construct(?string $executionId)
    {
        $this->executionId = $executionId;
    }

    public function serialize()
    {
        return json_encode([
            'executionId' => $this->executionId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->executionId = $json->executionId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $this->executionId);

        // fetch execution
        $executionManager = $commandContext->getExecutionManager();
        $execution = $executionManager->findExecutionById($this->executionId);
        EnsureUtil::ensureNotNull("execution " . $this->executionId . " doesn't exist", "execution", $execution);

        $this->checkGetActivityIds($execution, $commandContext);

        // fetch active activities
        return $execution->findActiveActivityIds();
    }

    protected function checkGetActivityIds(ExecutionEntity $execution, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessInstance($execution);
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
