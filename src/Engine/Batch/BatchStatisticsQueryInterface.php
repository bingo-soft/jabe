<?php

namespace BpmPlatform\Engine\Batch;

use BpmPlatform\Engine\Query\QueryInterface;

interface BatchStatisticsQueryInterface extends QueryInterface
{
    /**
     * Only select batch statistics for the given batch id.
     */
    public function batchId(string $batchId): BatchStatisticsQueryInterface;

    /**
     * Only select batch statistics of the given type.
     */
    public function type(string $type): BatchStatisticsQueryInterface;

    /** Only selects batch statistics with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): BatchStatisticsQueryInterface;

    /** Only selects batch statistics which have no tenant id. */
    public function withoutTenantId(): BatchStatisticsQueryInterface;

    /** Only selects batches which are active **/
    public function active(): BatchStatisticsQueryInterface;

    /** Only selects batches which are suspended **/
    public function suspended(): BatchStatisticsQueryInterface;

    /**
     * Returns batch statistics sorted by batch id; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderById(): BatchStatisticsQueryInterface;

    /**
     * Returns batch statistics sorted by tenant id; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderByTenantId(): BatchStatisticsQueryInterface;
}
