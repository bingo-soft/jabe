<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Util\ClockUtil;
use Concurrent\Queue\ArrayBlockingQueue;
use Concurrent\Executor\DefaultPoolExecutor;
use Concurrent\TimeUnit;

class DefaultJobExecutor extends ThreadPoolJobExecutor
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $queueSize = ArrayBlockingQueue::DEFAULT_CAPACITY;
    protected int $corePoolSize = 6;
    protected int $maxPoolSize = 18;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    protected function startExecutingJobs(...$args): void
    {
        $state = empty($args) ? $this->getState() : $args;
        if ($this->threadPoolExecutor === null || $this->threadPoolExecutor->isShutdown()) {
            $threadPoolQueue = new ArrayBlockingQueue($this->queueSize);
            $this->threadPoolExecutor = new DefaultPoolExecutor($this->corePoolSize, 0, TimeUnit::MILLISECONDS, $threadPoolQueue);
            //$this->threadPoolExecutor->setRejectedExecutionHandler(...);
            //getAcquireJobsRunnable()
            $this->threadPoolExecutor->setScopeArguments(...$state);
        }

        parent::startExecutingJobs(...$state);
    }

    protected function stopExecutingJobs(): void
    {
        parent::stopExecutingJobs();
         // Ask the thread pool to finish and exit
        $this->threadPoolExecutor->shutdown();

        // Waits for 1 minute to finish all currently executing jobs
        try {
            if (!$this->threadPoolExecutor->awaitTermination(60, TimeUnit::SECONDS)) {
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
