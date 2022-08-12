<?php

namespace Jabe\Impl;

use Jabe\History\{
    HistoricActivityInstanceInterface,
    NativeHistoricActivityInstanceQueryInterface
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class NativeHistoricActivityInstanceQueryImpl extends AbstractNativeQuery implements NativeHistoricActivityInstanceQueryInterface
{
    public function __construct($contextOrExecutor)
    {
        parent::__construct($contextOrExecutor);
    }

    //results ////////////////////////////////////////////////////////////////

    public function executeList(CommandContext $commandContext, array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $commandContext
            ->getHistoricActivityInstanceManager()
            ->findHistoricActivityInstancesByNativeQuery($parameterMap, $firstResult, $maxResults);
    }

    public function executeCount(CommandContext $commandContext, array $parameterMap): int
    {
        return $commandContext
            ->getHistoricActivityInstanceManager()
            ->findHistoricActivityInstanceCountByNativeQuery($parameterMap);
    }
}
