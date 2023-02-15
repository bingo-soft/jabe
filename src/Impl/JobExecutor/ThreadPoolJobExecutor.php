<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineImpl;
use Concurrent\ExecutorServiceInterface;

class ThreadPoolJobExecutor extends JobExecutor
{
    protected $threadPoolExecutor;

    public function __construct()
    {
        parent::__construct();
    }

    protected function startExecutingJobs(): void
    {
        $this->startJobAcquisitionThread();
    }

    protected function stopExecutingJobs(): void
    {
        $this->stopJobAcquisitionThread();
    }

    public function executeJobs(array $jobIds, ?ProcessEngineImpl $processEngine = null): void
    {
        try {
            $this->threadPoolExecutor->execute($this->getExecuteJobsRunnable($jobIds, $processEngine));
        } catch (\Exception $e) {
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
