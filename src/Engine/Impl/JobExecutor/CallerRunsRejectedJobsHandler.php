<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineImpl;

class CallerRunsRejectedJobsHandler implements RejectedJobsHandlerInterface
{
    public function jobsRejected(array $jobIds, ProcessEngineImpl $processEngine, JobExecutor $jobExecutor): void
    {
        $jobExecutor->getExecuteJobsRunnable($jobIds, $processEngine)->run();
    }
}
