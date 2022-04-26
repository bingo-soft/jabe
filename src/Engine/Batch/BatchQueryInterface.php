<?php

namespace Jabe\Engine\Batch;

use Jabe\Engine\Query\QueryInterface;

interface BatchQueryInterface extends QueryInterface
{
    /** Only select batch instances for the given batch id. */
    public function batchId(string $batchId): BatchQueryInterface;

    /**
     * Only select batches of the given type.
     */
    public function type(string $type): BatchQueryInterface;

    /** Only selects batches with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): BatchQueryInterface;

    /** Only selects batches which have no tenant id. */
    public function withoutTenantId(): BatchQueryInterface;

    /** Only selects batches which are active **/
    public function active(): BatchQueryInterface;

    /** Only selects batches which are suspended **/
    public function suspended(): BatchQueryInterface;

    /**
     * Returns batches sorted by id; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderById(): BatchQueryInterface;

    /**
     * Returns batches sorted by tenant id; must be followed by an invocation of {@link #asc()} or {@link #desc()}.
     */
    public function orderByTenantId(): BatchQueryInterface;
}
