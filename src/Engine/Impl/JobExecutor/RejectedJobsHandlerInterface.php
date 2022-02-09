<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\ProcessEngineImpl;

interface RejectedJobsHandlerInterface
{
    public function jobsRejected(array $jobIds, ProcessEngineImpl $processEngine, JobExecutor $jobExecutor): void;
}
