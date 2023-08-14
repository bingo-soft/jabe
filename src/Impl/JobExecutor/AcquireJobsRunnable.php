<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineLogger;
use Concurrent\RunnableInterface;

abstract class AcquireJobsRunnable implements RunnableInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobExecutor;
    protected bool $isInterrupted = false;
    protected $sharedMem;
    protected $monitor;
    protected $state = [];

    public function __construct(JobExecutor $jobExecutor, ...$args)
    {
        $this->jobExecutor = $jobExecutor;
        if (!empty($args)) {
            $this->state = $args;
        }
    }

    protected function suspendAcquisition(int $millis): void
    {
        if ($millis <= 0) {
            return;
        }
        if ($millis >= 1000) {
            $millis /= 1000;
            sleep(intval($millis));
        } else {
            usleep($millis);
        }
    }

    public function stop(): void
    {
    }

    public function jobWasAdded(): void
    {
        $this->state[1]->set(1);
    }

    protected function clearJobAddedNotification(): void
    {
        $this->state[1]->set(0);
    }

    public function isJobAdded(): \Swoole\Atomic
    {
        return $this->state[1];
    }
}
