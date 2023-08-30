<?php

namespace Jabe\Management;

use Jabe\Query\QueryInterface;

interface SchemaLogQueryInterface extends QueryInterface
{
    /**
     * Only show {@link SchemaLogEntry entries} with a given version.
     */
    public function version(?string $version): SchemaLogQueryInterface;

    /**
     * Order by task timestamp (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByTimestamp(): SchemaLogQueryInterface;
}
