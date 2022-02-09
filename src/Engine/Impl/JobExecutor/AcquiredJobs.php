<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

class AcquiredJobs
{
    protected $numberOfJobsAttemptedToAcquire;

    protected $acquiredJobBatches = [];
    protected $acquiredJobs = [];

    protected $numberOfJobsFailedToLock = 0;

    public function __construct(int $numberOfJobsAttemptedToAcquire)
    {
        $this->numberOfJobsAttemptedToAcquire = $numberOfJobsAttemptedToAcquire;
    }

    public function getJobIdBatches(): array
    {
        return $this->acquiredJobBatches;
    }

    public function addJobIdBatch($jobIds): void
    {
        if (is_array($jobIds)) {
            if (!empty($jobIds)) {
                $this->acquiredJobBatches[] = $jobIds;
                $this->acquiredJobs = array_merge($this->acquiredJobs, $jobIds);
            }
        } elseif (is_string($jobIds)) {
            $this->addJobIdBatch([$jobIds]);
        }
    }

    public function contains(string $jobId): bool
    {
        return in_array($jobId, $this->acquiredJobs);
    }

    public function size(): int
    {
        return count($this->acquiredJobs);
    }

    public function removeJobId(string $id): void
    {
        $this->numberOfJobsFailedToLock += 1;

        foreach ($this->acquiredJobs as $key => $val) {
            if ($val == $id) {
                unset($this->acquiredJobs[$key]);
                break;
            }
        }
        foreach ($this->acquiredJobBatches as $key1 => $batch) {
            foreach ($batch as $key2 => $val) {
                if ($val == $id) {
                    unset($this->acquiredJobBatches[$key1][$key2]);
                    break;
                }
            }
            if (empty($this->acquiredJobBatches[$key1])) {
                unset($this->acquiredJobBatches[$key1]);
            }
        }
    }

    public function getNumberOfJobsFailedToLock(): int
    {
        return $this->numberOfJobsFailedToLock;
    }

    public function getNumberOfJobsAttemptedToAcquire(): int
    {
        return $this->numberOfJobsAttemptedToAcquire;
    }
}
