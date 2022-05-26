<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineImpl;
use Jabe\Engine\Impl\Util\Concurrent\ProcessPoolExecutor;

class ThreadPoolJobExecutor extends JobExecutor
{
    protected $threadPoolExecutor;

    protected function startExecutingJobs(): void
    {
        $this->startJobAcquisitionThread();
    }

    protected function stopExecutingJobs(): void
    {
        $this->stopJobAcquisitionThread();
    }

    public function executeJobs(array $jobIds, ProcessEngineImpl $processEngine): void
    {
        try {
            $this->threadPoolExecutor->execute($this->getExecuteJobsRunnable($jobIds, $processEngine));
        } catch (\Exception $e) {
            $this->logRejectedExecution($processEngine, count($jobIds));
            $this->rejectedJobsHandler->jobsRejected($jobIds, $processEngine, $this);
        }
    }

    // getters / setters
    public function getThreadPoolExecutor(): ProcessPoolExecutor
    {
        return $this->threadPoolExecutor;
    }

    public function setThreadPoolExecutor(ProcessPoolExecutor $threadPoolExecutor): void
    {
        $this->threadPoolExecutor = $threadPoolExecutor;
    }
}
