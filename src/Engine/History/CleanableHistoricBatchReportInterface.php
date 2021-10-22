<?php

namespace BpmPlatform\Engine\History;

use BpmPlatform\Engine\Query\QueryInterface;

interface CleanableHistoricBatchReportInterface extends QueryInterface
{
    /**
     * Order by finished batch operations amount (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByFinishedBatchOperation(): CleanableHistoricBatchReportInterface;
}
