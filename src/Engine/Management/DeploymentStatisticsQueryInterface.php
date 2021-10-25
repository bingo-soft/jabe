<?php

namespace BpmPlatform\Engine\Management;

use BpmPlatform\Engine\Query\QueryInterface;

interface DeploymentStatisticsQueryInterface extends QueryInterface
{
    /**
     * Include an aggregation of failed jobs in the result.
     */
    public function includeFailedJobs(): DeploymentStatisticsQueryInterface;

    /**
     * Include an aggregation of incidents in the result.
     */
    public function includeIncidents(): DeploymentStatisticsQueryInterface;

    /**
     * Include an aggregation of incidents of the assigned incidentType in the result.
     */
    public function includeIncidentsForType(string $incidentType): DeploymentStatisticsQueryInterface;
}
