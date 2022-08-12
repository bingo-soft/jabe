<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Runtime\{
    NativeProcessInstanceQueryInterface,
    ProcessInstanceInterface
};

class NativeProcessInstanceQueryImpl extends AbstractNativeQuery implements NativeProcessInstanceQueryInterface
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
            ->findProcessInstanceByNativeQuery($parameterMap, $firstResult, $maxResults);
    }

    public function executeCount(CommandContext $commandContext, array $parameterMap): int
    {
        return $commandContext
            ->getExecutionManager()
            // can use execution count, since the result type doesn't matter
            ->findExecutionCountByNativeQuery($parameterMap);
    }
}
