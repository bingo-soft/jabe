<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Util\Concurrent\RunnableInterface;

abstract class AcquireJobsRunnable implements RunnableInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobExecutor;
    protected $isInterrupted = false;
    protected $isJobAdded = false;
    protected $monitor;

    public function __construct(JobExecutor $jobExecutor)
    {
        $this->jobExecutor = $jobExecutor;
    }

    protected function suspendAcquisition(int $millis): void
    {
    }

    public function stop(): void
    {
    }

    public function jobWasAdded(): void
    {
        $this->isJobAdded = true;
    }

    protected function clearJobAddedNotification(): void
    {
        $this->isJobAdded = false;
    }

    public function isJobAdded(): bool
    {
        return $this->isJobAdded;
    }
}
