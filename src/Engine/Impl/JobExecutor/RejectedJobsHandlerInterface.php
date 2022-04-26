<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineImpl;

interface RejectedJobsHandlerInterface
{
    public function jobsRejected(array $jobIds, ProcessEngineImpl $processEngine, JobExecutor $jobExecutor): void;
}
