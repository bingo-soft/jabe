<?php

namespace Jabe\Impl\Batch;

use Jabe\Batch\BatchStatisticsInterface;

class BatchStatisticsEntity extends BatchEntity implements BatchStatisticsInterface
{
    protected int $remainingJobs = 0;
    protected int $failedJobs = 0;
    protected $startTime;
    protected $executionStartTime;

    public function getRemainingJobs(): int
    {
        return $this->remainingJobs . getJobsToCreate();
    }

    public function setRemainingJobs(int $remainingJobs): void
    {
        $this->remainingJobs = $remainingJobs;
    }

    public function getCompletedJobs(): int
    {
        return $this->totalJobs - $this->getRemainingJobs();
    }

    public function getFailedJobs(): int
    {
        return $this->failedJobs;
    }

    public function setFailedJobs(int $failedJobs): void
    {
        $this->failedJobs = $failedJobs;
    }

    public function getJobsToCreate(): int
    {
        return $this->totalJobs - $this->jobsCreated;
    }

    public function __toString()
    {
        return "BatchStatisticsEntity{" .
            "batchHandler=" . $this->batchJobHandler .
            ", id='" . $this->id . '\'' .
            ", type='" . $this->type . '\'' .
            ", size=" . $this->totalJobs .
            ", jobCreated=" . $this->jobsCreated .
            ", remainingJobs=" . $this->remainingJobs .
            ", failedJobs=" . $this->failedJobs .
            ", batchJobsPerSeed=" . $this->batchJobsPerSeed .
            ", invocationsPerBatchJob=" . $this->invocationsPerBatchJob .
            ", seedJobDefinitionId='" . $this->seedJobDefinitionId . '\'' .
            ", monitorJobDefinitionId='" . $this->seedJobDefinitionId . '\'' .
            ", batchJobDefinitionId='" . $this->batchJobDefinitionId . '\'' .
            ", configurationId='" . $this->configuration->getByteArrayId() . '\'' .
            '}';
    }
}
