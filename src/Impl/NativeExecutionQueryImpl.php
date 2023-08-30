<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Runtime\{
    ExecutionInterface,
    NativeExecutionQueryInterface
};

class NativeExecutionQueryImpl extends AbstractNativeQuery implements NativeExecutionQueryInterface
{
    public function __construct($contextOrExecutor)
    {
        parent::__construct($contextOrExecutor);
    }

    //results ////////////////////////////////////////////////////////////////

    public function executeList(CommandContext $commandContext, array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $commandContext
            ->getExecutionManager()
            ->findExecutionsByNativeQuery($parameterMap, $firstResult, $maxResults);
    }

    public function executeCount(CommandContext $commandContext, array $parameterMap): int
    {
        return $commandContext
            ->getExecutionManager()
            ->findExecutionCountByNativeQuery($parameterMap);
    }
}
