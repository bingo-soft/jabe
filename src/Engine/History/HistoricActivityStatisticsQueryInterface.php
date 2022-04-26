<?php

namespace Jabe\Engine\History;

use Jabe\Engine\Query\QueryInterface;

interface HistoricActivityStatisticsQueryInterface extends QueryInterface
{
    /**
     * Include an aggregation of finished instances in the result.
     */
    public function includeFinished(): HistoricActivityStatisticsQueryInterface;

    /**
     * Include an aggregation of canceled instances in the result.
     */
    public function includeCanceled(): HistoricActivityStatisticsQueryInterface;

    /**
     * Include an aggregation of instances, which complete a scope (ie. in bpmn manner: an activity
     * which consumed a token and did not produced a new one), in the result.
     */
    public function includeCompleteScope(): HistoricActivityStatisticsQueryInterface;

    /** Include an aggregation of the incidents in the result. */
    public function includeIncidents(): HistoricActivityStatisticsQueryInterface;

    /** Only select historic activities of process instances that were started before the given date. */
    public function startedBefore(string $date): HistoricActivityStatisticsQueryInterface;

    /** Only select historic activities of process instances that were started after the given date. */
    public function startedAfter(string $date): HistoricActivityStatisticsQueryInterface;

    /** Only select historic activities of process instances that were finished before the given date. */
    public function finishedBefore(string $date): HistoricActivityStatisticsQueryInterface;

    /** Only select historic activities of process instances that were finished after the given date. */
    public function finishedAfter(string $date): HistoricActivityStatisticsQueryInterface;

    /** Only select historic activities of process instances with the given IDs */
    public function processInstanceIdIn(array $processInstanceIds): HistoricActivityStatisticsQueryInterface;

    /**
     * Order by activity id (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByActivityId(): HistoricActivityStatisticsQueryInterface;
}
