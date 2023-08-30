<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineImpl;

class NotifyAcquisitionRejectedJobsHandler implements RejectedJobsHandlerInterface
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
