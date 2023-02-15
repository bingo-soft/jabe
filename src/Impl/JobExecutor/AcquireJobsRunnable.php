<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineLogger;
use Concurrent\RunnableInterface;

abstract class AcquireJobsRunnable implements RunnableInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobExecutor;
    protected bool $isInterrupted = false;
    protected $isJobAdded;
    protected $monitor;

    public function __construct(JobExecutor $jobExecutor)
    {
        $this->jobExecutor = $jobExecutor;
        $this->isJobAdded = new \Swoole\Atomic(0);
    }

    protected function suspendAcquisition(int $millis): void
    {
        if ($millis <= 0) {
            return;
        }
        usleep(1000);
        $millis = $millis > 1000 ? 999 : $millis;
    }

    public function stop(): void
    {
    }

    public function jobWasAdded(): void
    {
        $this->isJobAdded->set(1);
    }

    protected function clearJobAddedNotification(): void
    {
        $this->isJobAdded->set(0);
    }

    public function isJobAdded(): bool
    {
        return $this->isJobAdded->get();
    }

    public function getIsJobAddedAtomic(): \Swoole\Atomic
    {
        return $this->isJobAdded;
    }
}
