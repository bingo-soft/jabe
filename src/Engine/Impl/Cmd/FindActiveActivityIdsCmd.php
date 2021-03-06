<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExecutionManager
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class FindActiveActivityIdsCmd implements CommandInterface, \Serializable
{
    protected $executionId;

    public function __construct(string $executionId)
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
}
