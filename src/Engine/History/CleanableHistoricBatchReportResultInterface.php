<?php

namespace BpmPlatform\Engine\History;

interface CleanableHistoricBatchReportResultInterface
{
    /**
     * Returns the batch type.
     */
    public function getBatchType(): string;

    /**
     * Returns the history time to live for the selected batch type.
     */
    public function getHistoryTimeToLive(): int;

    /**
     * Returns the amount of finished historic batches.
     */
    public function getFinishedBatchesCount(): int;

    /**
     * Returns the amount of cleanable historic batches.
     */
    public function getCleanableBatchesCount(): int;
}
