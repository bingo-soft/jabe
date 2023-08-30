<?php

namespace Jabe\Management;

use Jabe\Repository\DeploymentInterface;

interface DeploymentStatisticsInterface extends DeploymentInterface
{
    /**
     * The number of all process instances of the process definitions contained in this deployment.
     */
    public function getInstances(): int;

    /**
     * The number of all failed jobs of process instances of definitions contained in this deployment.
     */
    public function getFailedJobs(): int;

    /**
     * Returns a list of incident statistics.
     */
    public function getIncidentStatistics(): array;
}
