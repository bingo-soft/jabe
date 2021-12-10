<?php

namespace BpmPlatform\Engine\Management;

interface ActivityStatisticsInterface
{
    /**
     * The activity id.
     */
    public function getId(): ?string;

    /**
     * The number of all instances of the activity.
     */
    public function getInstances(): int;

    /**
     * The number of all failed jobs for the activity.
     */
    public function getFailedJobs(): int;

    /**
     * Returns a list of incident statistics.
     */
    public function getIncidentStatistics(): array;
}
