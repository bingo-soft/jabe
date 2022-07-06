<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandContextListenerInterface
};
use Jabe\Engine\Impl\Persistence\Entity\JobEntity;

class JobFailureCollector implements CommandContextListenerInterface
{
    protected $failure;
    protected $job;
    protected $jobId;
    protected $failedActivityId;

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    public function setFailure(\Throwable $failure): void
    {
        // log failure if not already present
        if ($this->failure === null) {
            $this->failure = $failure;
        }
    }

    public function getFailure(): Throwable
    {
        return $this->failure;
    }

    public function onCommandFailed(CommandContext $commandContext, \Throwable $t): void
    {
        $this->setFailure($t);
    }

    public function onCommandContextClose(CommandContext $commandContext): void
    {
        // ignore
    }

    public function setJob(JobEntity $job): void
    {
        $this->job = $job;
    }

    public function getJob(): JobEntity
    {
        return $this->job;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getFailedActivityId(): string
    {
        return $this->failedActivityId;
    }

    public function setFailedActivityId(string $activityId): void
    {
        $this->failedActivityId = $activityId;
    }
}
