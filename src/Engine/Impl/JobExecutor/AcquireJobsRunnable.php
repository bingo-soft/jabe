<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use parallel\Sync;

abstract class AcquireJobsRunnable implements RunnableInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobExecutor;

    protected $isInterrupted = false;
    protected $isJobAdded = false;
    protected $monitor;

    public function __construct(JobExecutor $jobExecutor)
    {
        $this->monitor = new Sync(false);
        $this->jobExecutor = $jobExecutor;
    }

    protected function suspendAcquisition(int $millis): void
    {
        if ($millis <= 0) {
            return;
        }
        try {
            //LOG.debugJobAcquisitionThreadSleeping(millis);
            if (!$this->isInterrupted) {
                $this->monitor->set(true);
                $this->monitor->wait(/*$millis*/);
            }
            //LOG.jobExecutorThreadWokeUp();
        } catch (\Exception $e) {
            //LOG.jobExecutionWaitInterrupted();
            throw $e;
        } finally {
            $this->monitor->set(false);
        }
    }

    public function stop(): void
    {
        $this->isInterrupted = true;
        $prev = $this->monitor->get();
        if ($prev) {
            $this->monitor->set(false);
            $this->monitor->notify(true);
        }
    }

    public function jobWasAdded(): void
    {
        $this->isJobAdded = true;
        $prev = $this->monitor->get();
        if ($prev) {
            $this->monitor->set(false);
            $this->monitor->notify(true);
        }
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
