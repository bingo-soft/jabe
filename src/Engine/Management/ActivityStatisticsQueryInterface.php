<?php

namespace Jabe\Engine\Management;

use Jabe\Engine\Query\QueryInterface;

interface ActivityStatisticsQueryInterface extends QueryInterface
{
    /**
     * Include an aggregation of failed jobs in the result.
     */
    public function includeFailedJobs(): ActivityStatisticsQueryInterface;

    /**
     * Include an aggregation of incidents in the result.
     */
    public function includeIncidents(): ActivityStatisticsQueryInterface;

    /**
     * Include an aggregation of incidents of the assigned incidentType in the result.
     */
    public function includeIncidentsForType(string $incidentType): ActivityStatisticsQueryInterface;
}
