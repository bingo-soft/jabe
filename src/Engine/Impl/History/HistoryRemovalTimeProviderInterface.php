<?php

namespace BpmPlatform\Engine\Impl\History;

use BpmPlatform\Engine\Impl\Batch\History\HistoricBatchEntity;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoricProcessInstanceEventEntity
};
use BpmPlatform\Engine\Repository\{
    ProcessDefinitionInterface
};

interface HistoryRemovalTimeProviderInterface
{
    /**
     * Calculates the removal time of historic entities or batches.
     *
     * @param mixed $historicRootInstance
     * @param mixed $definition
     *
     * @return the removal time for historic process instances
     */
    public function calculateRemovalTime($instance, $definition = null): string;
}
