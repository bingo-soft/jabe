<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineLogger;

class DefaultJobExecutor extends ThreadPoolJobExecutor
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $queueSize = 4;
    protected $corePoolSize = 4;
    protected $maxPoolSize = 10;

    protected function startExecutingJobs(): void
    {
        parent::startExecutingJobs();
    }

    protected function stopExecutingJobs(): void
    {
        parent::stopExecutingJobs();
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getQueueSize(): int
    {
        return $this->queueSize;
    }

    public function setQueueSize(int $queueSize): void
    {
        $this->queueSize = $queueSize;
    }

    public function getCorePoolSize(): int
    {
        return $this->corePoolSize;
    }

    public function setCorePoolSize(int $corePoolSize): void
    {
        $this->corePoolSize = $corePoolSize;
    }

    public function getMaxPoolSize(): int
    {
        return $this->maxPoolSize;
    }

    public function setMaxPoolSize(int $maxPoolSize): void
    {
        $this->maxPoolSize = $maxPoolSize;
    }
}
