<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use Composer\Autoload\ClassLoader;
use parallel\Runtime;
use BpmPlatform\Engine\Impl\ProcessEngineImpl;

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
            $scope = $this;
            $this->threadPoolExecutor->run(function () use ($scope, $jobIds, $processEngine) {
                $jobs = $scope->getExecuteJobsRunnable($jobIds, $processEngine);
                $jobs->run();
            });
        } catch (\Exception $e) {
            $this->logRejectedExecution($processEngine, count($jobIds));
            $this->rejectedJobsHandler->jobsRejected($jobIds, $processEngine, $this);
        }
    }

    // getters / setters

    public function getThreadPoolExecutor(): Runtime
    {
        return $this->threadPoolExecutor;
    }

    public function setThreadPoolExecutor(Runtime $threadPoolExecutor): void
    {
        $this->threadPoolExecutor = $threadPoolExecutor;
    }
}
