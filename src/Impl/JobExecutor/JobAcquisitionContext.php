<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Util\CollectionUtil;

class JobAcquisitionContext
{
    protected $rejectedJobBatchesByEngine = [];
    protected $acquiredJobsByEngine = [];
    protected $additionalJobBatchesByEngine = [];
    protected $acquisitionException;
    protected $acquisitionTime;
    protected $isJobAdded;

    public function __construct()
    {
        $this->isJobAdded = new \Swoole\Atomic(0);
    }

    public function submitRejectedBatch(?string $engineName, array $jobIds): void
    {
        CollectionUtil::addToMapOfLists($this->rejectedJobBatchesByEngine, $engineName, $jobIds);
    }

    public function submitAcquiredJobs(?string $engineName, AcquiredJobs $acquiredJobs): void
    {
        $this->acquiredJobsByEngine[$engineName] = $acquiredJobs;
    }

    public function submitAdditionalJobBatch(?string $engineName, array $jobIds): void
    {
        CollectionUtil::addToMapOfLists($this->additionalJobBatchesByEngine, $engineName, $jobIds);
    }

    public function reset(): void
    {
        $this->additionalJobBatchesByEngine = [];

        // jobs that were rejected in the previous acquisition cycle
        // are to be resubmitted for execution in the current cycle
        $this->additionalJobBatchesByEngine = array_merge($this->additionalJobBatchesByEngine, $this->rejectedJobBatchesByEngine);

        $this->rejectedJobBatchesByEngine = [];
        $this->acquiredJobsByEngine = [];
        $this->acquisitionException = null;
        $this->acquisitionTime = 0;
        $this->isJobAdded->set(0);
    }

    /**
     * @return bool true, if for all engines there were less jobs acquired than requested
     */
    public function areAllEnginesIdle(): bool
    {
        foreach ($this->acquiredJobsByEngine as $engineName => $acquiredJobs) {
            $jobsAcquired = count($acquiredJobs->getJobIdBatches()) + $acquiredJobs->getNumberOfJobsFailedToLock();

            if ($jobsAcquired >= $acquiredJobs->getNumberOfJobsAttemptedToAcquire()) {
                return false;
            }
        }

        return true;
    }

    /**
     * true if at least one job could not be locked, regardless of engine
     */
    public function hasJobAcquisitionLockFailureOccurred(): bool
    {
        foreach ($this->acquiredJobsByEngine as $engineName => $acquiredJobs) {
            if ($acquiredJobs->getNumberOfJobsFailedToLock() > 0) {
                return true;
            }
        }

        return false;
    }

    // getters and setters

    public function setAcquisitionTime(int $acquisitionTime): void
    {
        $this->acquisitionTime = $acquisitionTime;
    }

    public function getAcquisitionTime(): int
    {
        return $this->acquisitionTime;
    }

    /**
     * Jobs that were acquired in the current acquisition cycle.
     * @return
     */
    public function getAcquiredJobsByEngine(): array
    {
        return $this->acquiredJobsByEngine;
    }

    /**
     * Jobs that were rejected from execution in the acquisition cycle
     * due to lacking execution resources.
     * With an execution thread pool, these jobs could not be submitted due to
     * saturation of the underlying job queue.
     */
    public function getRejectedJobsByEngine(): array
    {
        return $this->rejectedJobBatchesByEngine;
    }

    /**
     * Jobs that have been acquired in previous cycles and are supposed to
     * be re-submitted for execution
     */
    public function getAdditionalJobsByEngine(): array
    {
        return $this->additionalJobBatchesByEngine;
    }

    public function setAcquisitionException(\Exception $e): void
    {
        $this->acquisitionException = $e;
    }

    public function getAcquisitionException(): ?\Exception
    {
        return $this->acquisitionException;
    }

    public function setJobAdded(\Swoole\Atomic $isJobAdded): void
    {
        $this->isJobAdded = $isJobAdded;
    }

    public function isJobAdded(): bool
    {
        return $this->isJobAdded->get();
    }
}
