<?php

namespace Jabe\Engine\Impl\JobExecutor;

class BackoffJobAcquisitionStrategy implements JobAcquisitionStrategyInterface
{
    public const DEFAULT_EXECUTION_SATURATION_WAIT_TIME = 100;

    /*
     * all wait times are in milliseconds
     */

    /*
     * managing the idle level
     */
    protected $baseIdleWaitTime;
    protected $idleIncreaseFactor;
    protected $idleLevel;
    protected $maxIdleLevel;
    protected $maxIdleWaitTime;

    /*
     * managing the backoff level
     */
    protected $baseBackoffWaitTime;
    protected $backoffIncreaseFactor;
    protected $backoffLevel;
    protected $maxBackoffLevel;
    protected $maxBackoffWaitTime;
    protected $applyJitter = false;

    /*
     * Keeping a history of recent acquisitions without locking failure
     * for backoff level decrease
     */
    protected $numAcquisitionsWithoutLockingFailure = 0;
    protected $backoffDecreaseThreshold;

    protected $baseNumJobsToAcquire;

    protected $jobsToAcquire = [];

    /*
     * Backing off when the execution resources (queue) are saturated
     * in order to not busy wait for free resources
     */
    protected $executionSaturated = false;
    protected $executionSaturationWaitTime = self::DEFAULT_EXECUTION_SATURATION_WAIT_TIME;

    public function __construct(
        int $baseIdleWaitTime,
        float $idleIncreaseFactor,
        int $maxIdleTime,
        int $baseBackoffWaitTime,
        float $backoffIncreaseFactor,
        int $maxBackoffTime,
        int $backoffDecreaseThreshold,
        int $baseNumJobsToAcquire
    ) {

        $this->baseIdleWaitTime = $baseIdleWaitTime;
        $this->idleIncreaseFactor = $idleIncreaseFactor;
        $this->idleLevel = 0;
        $this->maxIdleWaitTime = $maxIdleTime;

        $this->baseBackoffWaitTime = $baseBackoffWaitTime;
        $this->backoffIncreaseFactor = $backoffIncreaseFactor;
        $this->backoffLevel = 0;
        $this->maxBackoffWaitTime = $maxBackoffTime;
        $this->backoffDecreaseThreshold = $backoffDecreaseThreshold;

        $this->baseNumJobsToAcquire = $baseNumJobsToAcquire;

        $this->initializeMaxLevels();
    }

    /*public function __construct(JobExecutor jobExecutor) {
        this(jobExecutor.getWaitTimeInMillis(),
            jobExecutor.getWaitIncreaseFactor(),
            jobExecutor.getMaxWait(),
            jobExecutor.getBackoffTimeInMillis(),
            jobExecutor.getWaitIncreaseFactor(),
            jobExecutor.getMaxBackoff(),
            jobExecutor.getBackoffDecreaseThreshold(),
            jobExecutor.getMaxJobsPerAcquisition());
    }*/

    protected function initializeMaxLevels(): void
    {
        if ($this->baseIdleWaitTime > 0 && $this->maxIdleWaitTime > 0 && $this->idleIncreaseFactor > 0 && $this->maxIdleWaitTime >= $this->baseIdleWaitTime) {
            // the maximum level that produces an idle time <= maxIdleTime:
            // see class docs for an explanation
            $this->maxIdleLevel = $this->log($this->idleIncreaseFactor, $this->maxIdleWaitTime / $this->baseIdleWaitTime) + 1;

            // + 1 to get the minimum level that produces an idle time > maxIdleTime
            $this->maxIdleLevel += 1;
        } else {
            $this->maxIdleLevel = 0;
        }

        if ($this->baseBackoffWaitTime > 0 && $this->maxBackoffWaitTime > 0 && $this->backoffIncreaseFactor > 0 && $this->maxBackoffWaitTime >= $this->baseBackoffWaitTime) {
            // the maximum level that produces a backoff time < maxBackoffTime:
            // see class docs for an explanation
            $this->maxBackoffLevel = $this->log($this->backoffIncreaseFactor, $this->maxBackoffWaitTime / $this->baseBackoffWaitTime) + 1;

            // + 1 to get the minimum level that produces a backoff time > maxBackoffTime
            $this->maxBackoffLevel += 1;
        } else {
            $this->maxBackoffLevel = 0;
        }
    }

    protected function log(float $base, float $value): float
    {
        return log10($value) / log10($base);
    }

    public function reconfigure(JobAcquisitionContext $context): void
    {
        $this->reconfigureIdleLevel($context);
        $this->reconfigureBackoffLevel($context);
        $this->reconfigureNumberOfJobsToAcquire($context);
        $this->executionSaturated = $this->allSubmittedJobsRejected($context);
    }

    /**
     * @return true, if all acquired jobs (spanning all engines) were rejected for execution
     */
    protected function allSubmittedJobsRejected(JobAcquisitionContext $context): bool
    {
        foreach ($context->getAcquiredJobsByEngine() as $engineName => $acquiredJobs) {
            $acquiredJobBatches = $acquiredJobs->getJobIdBatches();
            $additionalJobsByEngine = $context->getAdditionalJobsByEngine();
            $resubmittedJobBatches = null;
            if (array_key_exists($engineName, $additionalJobsByEngine)) {
                $resubmittedJobBatches = $additionalJobsByEngine[$engineName];
            }
            $rejectedJobsByEngine = $context->getRejectedJobsByEngine();
            $rejectedJobBatches = null;
            if (array_key_exists($engineName, $rejectedJobsByEngine)) {
                $rejectedJobBatches = $rejectedJobsByEngine[$engineName];
            }

            $numJobsSubmittedForExecution = count($acquiredJobBatches);
            if ($resubmittedJobBatches !== null) {
                $numJobsSubmittedForExecution += count($resubmittedJobBatches);
            }

            $numJobsRejected = 0;
            if ($rejectedJobBatches !== null) {
                $numJobsRejected += count($rejectedJobBatches);
            }

            // if not all jobs scheduled for execution have been rejected
            if ($numJobsRejected == 0 || $numJobsSubmittedForExecution > $numJobsRejected) {
                return false;
            }
        }

        return true;
    }

    protected function reconfigureIdleLevel(JobAcquisitionContext $context): void
    {
        if ($context->isJobAdded()) {
            $this->idleLevel = 0;
        } else {
            if ($context->areAllEnginesIdle() || $context->getAcquisitionException() !== null) {
                if ($this->idleLevel < $this->maxIdleLevel) {
                    $this->idleLevel += 1;
                }
            } else {
                $this->idleLevel = 0;
            }
        }
    }

    protected function reconfigureBackoffLevel(JobAcquisitionContext $context): void
    {
        // if for any engine, jobs could not be locked due to optimistic locking, back off

        if ($context->hasJobAcquisitionLockFailureOccurred()) {
            $this->numAcquisitionsWithoutLockingFailure = 0;
            $this->applyJitter = true;
            if ($this->backoffLevel < $this->maxBackoffLevel) {
                $this->backoffLevel += 1;
            }
        } else {
            $this->applyJitter = false;
            $this->numAcquisitionsWithoutLockingFailure += 1;
            if ($this->numAcquisitionsWithoutLockingFailure >= $this->backoffDecreaseThreshold && $this->backoffLevel > 0) {
                $this->backoffLevel -= 1;
                $this->numAcquisitionsWithoutLockingFailure = 0;
            }
        }
    }

    protected function reconfigureNumberOfJobsToAcquire(JobAcquisitionContext $context): void
    {
        // calculate the number of jobs to acquire next time
        $this->jobsToAcquire = [];
        foreach ($context->getAcquiredJobsByEngine() as $engineName => $acquiredJobs) {
            $numJobsToAcquire = $this->baseNumJobsToAcquire * pow($this->backoffIncreaseFactor, $this->backoffLevel);
            $rejectedJobBatchesForEngine = null;
            $rejectedJobsByEngine = $context->getRejectedJobsByEngine();
            if (array_key_exists($engineName, $rejectedJobsByEngine)) {
                $rejectedJobBatchesForEngine = $rejectedJobsByEngine[$engineName];
            }
            if ($rejectedJobBatchesForEngine !== null) {
                $numJobsToAcquire -= count($rejectedJobBatchesForEngine);
            }
            $numJobsToAcquire = max(0, $numJobsToAcquire);

            $this->jobsToAcquire[$engineName] = $numJobsToAcquire;
        }
    }

    public function getWaitTime(): int
    {
        if ($this->idleLevel > 0) {
            return $this->calculateIdleTime();
        } elseif ($this->backoffLevel > 0) {
            return $this->calculateBackoffTime();
        } elseif ($this->executionSaturated) {
            return $this->executionSaturationWaitTime;
        } else {
            return 0;
        }
    }

    protected function calculateIdleTime(): int
    {
        if ($this->idleLevel <= 0) {
            return 0;
        } elseif ($this->idleLevel >= $this->maxIdleLevel) {
            return $this->maxIdleWaitTime;
        } else {
            return $this->baseIdleWaitTime * pow($this->idleIncreaseFactor, $this->idleLevel - 1);
        }
    }

    protected function calculateBackoffTime(): int
    {
        $backoffTime = 0;

        if ($this->backoffLevel <= 0) {
            $backoffTime = 0;
        } elseif ($this->backoffLevel >= $this->maxBackoffLevel) {
            $backoffTime = $this->maxBackoffWaitTime;
        } else {
            $backoffTime = $this->baseBackoffWaitTime * pow($this->backoffIncreaseFactor, $this->backoffLevel - 1);
        }

        if ($this->applyJitter) {
            // add a bounded random jitter to avoid multiple job acquisitions getting exactly the same
            // polling interval
            $backoffTime += rand() * ($backoffTime / 2);
        }

        return $backoffTime;
    }

    public function getNumJobsToAcquire(string $processEngine): int
    {
        $numJobsToAcquire = null;
        if (array_key_exists($processEngine, $this->jobsToAcquire)) {
            $numJobsToAcquire = $this->jobsToAcquire[$processEngine];
            return $this->numJobsToAcquire;
        }
        return $this->baseNumJobsToAcquire;
    }
}
