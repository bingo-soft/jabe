<?php

namespace Jabe\Impl;

use Jabe\History\{
    HistoricTaskInstanceInterface,
    HistoricTaskInstanceQueryInterface
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class NativeHistoricTaskInstanceQueryImpl extends AbstractNativeQuery implements NativeHistoricTaskInstanceQueryInterface
{
    public function __construct($contextOrExecutor)
    {
        parent::__construct($contextOrExecutor);
    }

   //results ////////////////////////////////////////////////////////////////

    public function executeList(CommandContext $commandContext, array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $commandContext
            ->getHistoricTaskInstanceManager()
            ->findHistoricTaskInstancesByNativeQuery($parameterMap, $firstResult, $maxResults);
    }

    public function executeCount(CommandContext $commandContext, array $parameterMap): int
    {
        return $commandContext
            ->getHistoricTaskInstanceManager()
            ->findHistoricTaskInstanceCountByNativeQuery($parameterMap);
    }
}
