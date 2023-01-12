<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\JobEntity;
use Jabe\Impl\Util\EnsureUtil;

class UnlockJobCmd implements CommandInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobId;

    public function __construct(?string $jobId)
    {
        $this->jobId = $jobId;
    }

    protected function getJob(): ?JobEntity
    {
        return Context::getCommandContext()->getJobManager()->findJobById($this->jobId);
    }

    public function execute(CommandContext $commandContext)
    {
        $job = $this->getJob();

        if (Context::getJobExecutorContext() === null) {
            EnsureUtil::ensureNotNull("Job with id " . $this->jobId . " does not exist", "job", $job);
        } elseif (Context::getJobExecutorContext() !== null && $job === null) {
            // CAM-1842
            // Job was acquired but does not exist anymore. This is not a problem.
            // It usually means that the job has been deleted after it was acquired which can happen if the
            // the activity instance corresponding to the job is cancelled.
            //LOG.debugAcquiredJobNotFound(jobId);
            return null;
        }

        $job->unlock();

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
