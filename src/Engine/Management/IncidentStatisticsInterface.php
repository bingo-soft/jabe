<?php

namespace BpmPlatform\Engine\Management;

interface IncidentStatisticsInterface
{
    /**
     * Returns the type of the incidents.
     */
    public function getIncidentType(): string;

    /**
     * Returns the number of incidents to the corresponding
     * incidentType.
     */
    public function getIncidentCount(): int;
}
