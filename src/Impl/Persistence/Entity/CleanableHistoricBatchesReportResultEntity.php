<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\CleanableHistoricBatchReportResultInterface;
use Jabe\Impl\Util\ClassNameUtil;

class CleanableHistoricBatchesReportResultEntity implements CleanableHistoricBatchReportResultInterface
{
    protected $batchType;
    protected $historyTimeToLive;
    protected $finishedBatchesCount;
    protected $cleanableBatchesCount;

    public function getBatchType(): ?string
    {
        return $this->batchType;
    }

    public function setBatchType(?string $batchType): void
    {
        $this->batchType = $batchType;
    }

    public function getHistoryTimeToLive(): ?int
    {
        return $this->historyTimeToLive;
    }

    public function setHistoryTimeToLive(?int $historyTimeToLive): void
    {
        $this->historyTimeToLive = $historyTimeToLive;
    }

    public function getFinishedBatchesCount(): int
    {
        return $this->finishedBatchesCount;
    }

    public function setFinishedBatchesCount(int $finishedBatchCount): void
    {
        $this->finishedBatchesCount = $finishedBatchCount;
    }

    public function getCleanableBatchesCount(): int
    {
        return $this->cleanableBatchesCount;
    }

    public function setCleanableBatchesCount(int $cleanableBatchCount): void
    {
        $this->cleanableBatchesCount = $cleanableBatchCount;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[batchType = " . $this->batchType
            . ", historyTimeToLive = " . $this->historyTimeToLive
            . ", finishedBatchesCount = " . $this->finishedBatchesCount
            . ", cleanableBatchesCount = " . $this->cleanableBatchesCount
            . "]";
    }
}
