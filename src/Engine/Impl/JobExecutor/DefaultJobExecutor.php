<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Util\Concurrent\{
    ArrayBlockingQueue,
    ProcessPoolExecutor,
    TimeUnit
};

class DefaultJobExecutor extends ThreadPoolJobExecutor
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $queueSize = PHP_INT_MAX;
    protected $corePoolSize = 4;
    protected $maxPoolSize = 10;

    protected function startExecutingJobs(): void
    {
        if ($this->threadPoolExecutor == null || $this->threadPoolExecutor->isShutdown()) {
            $threadPoolQueue = new ArrayBlockingQueue($this->queueSize);
            $this->threadPoolExecutor = new ProcessPoolExecutor($corePoolSize, 0, TimeUnit::MILLISECONDS, $threadPoolQueue);
            //$this->threadPoolExecutor->setRejectedExecutionHandler(...);
        }

        parent::startExecutingJobs();
    }

    protected function stopExecutingJobs(): void
    {
        parent::stopExecutingJobs();
         // Ask the thread pool to finish and exit
        $this->threadPoolExecutor->shutdown();

        // Waits for 1 minute to finish all currently executing jobs
        try {
            if (!$threadPoolExecutor->awaitTermination(60, TimeUnit::SECONDS)) {
                //LOG.timeoutDuringShutdown();
            }
        } catch (\Exception $e) {
            //LOG.interruptedWhileShuttingDownjobExecutor(e);
        }
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
