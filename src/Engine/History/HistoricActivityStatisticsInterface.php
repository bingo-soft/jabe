<?php

namespace Jabe\Engine\History;

interface HistoricActivityStatisticsInterface
{
    /**
     * The activity id.
     */
    public function getId(): string;

    /**
     * The number of all running instances of the activity.
     */
    public function getInstances(): int;

    /**
     * The number of all finished instances of the activity.
     */
    public function getFinished(): int;

    /**
     * The number of all canceled instances of the activity.
     */
    public function getCanceled(): int;

    /**
     * The number of all instances, which complete a scope (ie. in bpmn manner: an activity
     * which consumed a token and did not produced a new one), of the activity.
     */
    public function getCompleteScope(): int;

    /** The number of open incidents of the activity. */
    public function getOpenIncidents(): int;

    /** The number of resolved incidents of the activity. */
    public function getResolvedIncidents(): int;

    /** The number of deleted incidents of the activity. */
    public function getDeletedIncidents(): int;
}
