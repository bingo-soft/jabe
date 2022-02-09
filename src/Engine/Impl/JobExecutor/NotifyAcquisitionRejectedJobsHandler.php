<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\ProcessEngineImpl;

class NotifyAcquisitionRejectedJobsHandler implements RejectedJobsHandler
{
    public function jobsRejected(array $jobIds, ProcessEngineImpl $processEngine, JobExecutor $jobExecutor): void
    {
        $acquireJobsRunnable = $jobExecutor->getAcquireJobsRunnable();
        if ($acquireJobsRunnable instanceof SequentialJobAcquisitionRunnable) {
            $context = $acquireJobsRunnable->getAcquisitionContext();
            $context->submitRejectedBatch($processEngine->getName(), $jobIds);
        } else {
            $jobExecutor->getExecuteJobsRunnable($jobIds, $processEngine)->run();
        }
    }
}
