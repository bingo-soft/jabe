<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Task\{
    NativeTaskQueryInterface,
    TaskInterface
};

class NativeTaskQueryImpl extends AbstractNativeQuery implements NativeTaskQueryInterface
{

    public function __construct($contextOrExecutor)
    {
        parent::__construct($contextOrExecutor);
    }

    //results ////////////////////////////////////////////////////////////////

    public function executeList(CommandContext $commandContext, array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $commandContext
            ->getTaskManager()
            ->findTasksByNativeQuery($parameterMap, $firstResult, $maxResults);
    }

    public function executeCount(CommandContext $commandContext, array $parameterMap): int
    {
        return $commandContext
            ->getTaskManager()
            ->findTaskCountByNativeQuery($parameterMap);
    }
}
