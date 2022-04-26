<?php

namespace Jabe\Engine\Management;

interface MetricsQueryInterface
{
    /**
     * @see constants in {@link Metrics} for a list of names which can be used here.
     *
     * @param name The name of the metrics to query for
     */
    public function name(string $name): MetricsQueryInterface;

    /**
     * Restrict to data collected by the reported with the given identifier
     */
    public function reporter(string $reporter): MetricsQueryInterface;

    /**
     * Restrict to data collected after the given date (inclusive)
     */
    public function startDate(string $startTime): MetricsQueryInterface;

    /**
     * Restrict to data collected before the given date (exclusive)
     */
    public function endDate(string $endTime): MetricsQueryInterface;


    /**
     * Sets the offset of the returned results.
     *
     * @param offset indicates after which row the result begins
     * @return the adjusted MetricsQuery
     */
    public function offset(int $offset): MetricsQueryInterface;

    /**
     * Sets the limit row count of the result.
     * Can't be set larger than 200, since it is the maximum row count which should be returned.
     *
     * @param maxResults the new row limit of the result
     * @return the adjusted MetricsQuery
     */
    public function limit(int $maxResults): MetricsQueryInterface;

    /**
     * Aggregate metrics by reporters
     *
     * @return the adjusted MetricsQuery
     */
    public function aggregateByReporter(): MetricsQueryInterface;

    /**
     * Returns the metrics summed up and aggregated on a time interval.
     * Default interval is 900 (15 minutes). The list size has a maximum of 200
     * the maximum can be decreased with the MetricsQuery#limit method. Paging
     * is enabled with the help of the offset.
     *
     * @return the aggregated metrics
     */
    public function interval(?int $interval = null): array;

    /**
     * @return the aggregated sum
     */
    public function sum(): int;
}
