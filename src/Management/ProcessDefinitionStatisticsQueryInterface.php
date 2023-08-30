<?php

namespace Jabe\Management;

use Jabe\Query\QueryInterface;

interface ProcessDefinitionStatisticsQueryInterface extends QueryInterface
{
    /**
     * Include an aggregation of failed jobs in the result.
     */
    public function includeFailedJobs(): ProcessDefinitionStatisticsQueryInterface;

    /**
     * Include an aggregation of incidents in the result.
     */
    public function includeIncidents(): ProcessDefinitionStatisticsQueryInterface;

    /**
     * Include an aggregation of root incidents only
     */
    public function includeRootIncidents(): ProcessDefinitionStatisticsQueryInterface;

    /**
     * Include an aggregation of incidents of the assigned incidentType in the result.
     */
    public function includeIncidentsForType(?string $incidentType): ProcessDefinitionStatisticsQueryInterface;
}
