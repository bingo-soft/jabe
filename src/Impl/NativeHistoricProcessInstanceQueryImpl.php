<?php

namespace Jabe\Impl;

use Jabe\History\{
    HistoricProcessInstanceInterface,
    HistoricProcessInstanceQueryInterface
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class NativeHistoricProcessInstanceQueryImpl extends AbstractNativeQuery implements NativeHistoricProcessInstanceQueryInterface
{

    public function __construct($contextOrExecutor)
    {
        parent::__construct($contextOrExecutor);
    }

    //results ////////////////////////////////////////////////////////////////

    public function executeList(CommandContext $commandContext, array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findHistoricProcessInstancesByNativeQuery($parameterMap, $firstResult, $maxResults);
    }

    public function executeCount(CommandContext $commandContext, array $parameterMap): int
    {
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findHistoricProcessInstanceCountByNativeQuery($parameterMap);
    }
}
