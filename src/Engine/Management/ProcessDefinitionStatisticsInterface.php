<?php

namespace BpmPlatform\Engine\Management;

use BpmPlatform\Engine\Repository\ProcessDefinitionInterface;

interface ProcessDefinitionStatisticsInterface extends ProcessDefinitionInterface
{
    /**
     * The number of all process instances of the process definition.
     */
    public function getInstances(): int;

    /**
     * The number of all failed jobs of all process instances.
     */
    public function getFailedJobs(): int;

    /**
     * Returns a list of incident statistics.
     */
    public function getIncidentStatistics(): array;
}
