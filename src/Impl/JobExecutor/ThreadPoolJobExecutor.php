<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineImpl;
use Concurrent\ExecutorServiceInterface;

class ThreadPoolJobExecutor extends JobExecutor
{
    protected $threadPoolExecutor;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    protected function startExecutingJobs(...$args): void
    {
        $this->startJobAcquisitionThread(...$args);
    }

    protected function stopExecutingJobs(): void
    {
        $this->stopJobAcquisitionThread();
    }

    public function executeJobs(array $jobIds, ?ProcessEngineImpl $processEngine = null, ...$args): void
    {
        try {
            $runnable = $this->getExecuteJobsRunnable($jobIds, $processEngine);
            $this->threadPoolExecutor->execute($runnable);
        } catch (\Throwable $e) {
            $this->logRejectedExecution($processEngine, count($jobIds));
            $this->rejectedJobsHandler->jobsRejected($jobIds, $processEngine, $this);
        }
    }

    // getters / setters
    public function getThreadPoolExecutor(): ExecutorServiceInterface
    {
        return $this->threadPoolExecutor;
    }

    public function setThreadPoolExecutor(ExecutorServiceInterface $threadPoolExecutor): void
    {
        $this->threadPoolExecutor = $threadPoolExecutor;
    }
}
