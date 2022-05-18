<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineImpl;

class ThreadPoolJobExecutor extends JobExecutor
{
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
            $runnable = $this->getExecuteJobsRunnable($jobIds, $processEngine);
            if (Coroutine::getCid() == -1) {
                Coroutine\run(function () use ($runnable) {
                    go(function () use ($runnable) {
                        $runnable->run();
                    });
                });
            } else {
                go(function () use ($runnable) {
                    $runnable->run();
                });
            }
        } catch (\Exception $e) {
            $this->logRejectedExecution($processEngine, count($jobIds));
            $this->rejectedJobsHandler->jobsRejected($jobIds, $processEngine, $this);
        }
    }
}
