<?php

namespace BpmPlatform\Engine\Batch\History;

use BpmPlatform\Engine\Query\QueryInterface;

interface HistoricBatchQueryInterface extends QueryInterface
{
    /**
     * Only select historic batch instances for the given batch id.
     */
    public function batchId(string $batchId): HistoricBatchQueryInterface;

    /**
     * Only select historic batches of the given type.
     */
    public function type(string $type): HistoricBatchQueryInterface;

    /**
     * Only select historic batches which are completed or not.
     */
    public function completed(bool $completed): HistoricBatchQueryInterface;

    /** Only selects historic batches with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricBatchQueryInterface;

    /** Only selects historic batches which have no tenant id. */
    public function withoutTenantId(): HistoricBatchQueryInterface;

    /**
     * Returns historic batches sorted by id; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderById(): HistoricBatchQueryInterface;

    /**
     * Returns historic batches sorted by start time; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderByStartTime(): HistoricBatchQueryInterface;

    /**
     * Returns historic batches sorted by end time; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderByEndTime(): HistoricBatchQueryInterface;

    /**
     * Returns historic batches sorted by tenant id; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderByTenantId(): HistoricBatchQueryInterface;
}
