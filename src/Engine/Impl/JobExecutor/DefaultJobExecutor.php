<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Composer\Autoload\ClassLoader;
use parallel\Runtime;
use Jabe\Engine\Impl\ProcessEngineLogger;

class DefaultJobExecutor extends ThreadPoolJobExecutor
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $queueSize = 3;
    protected $corePoolSize = 3;
    protected $maxPoolSize = 10;

    protected function startExecutingJobs(): void
    {
        if ($this->threadPoolExecutor == null/*|| threadPoolExecutor.isShutdown()*/) {
            $reflector = new \ReflectionClass(ClassLoader::class);
            $vendorPath = preg_replace('/^(.*)\/composer\/ClassLoader\.php$/', '$1', $reflector->getFileName());
            $this->threadPoolExecutor = new Runtime($vendorPath . '/autoload.php');
        }

        parent::startExecutingJobs();
    }

    protected function stopExecutingJobs(): void
    {
        parent::stopExecutingJobs();

        // Ask the thread pool to finish and exit
        //threadPoolExecutor.shutdown();
        $this->threadPoolExecutor->close();

        // Waits for 1 minute to finish all currently executing jobs
        try {
            sleep(60);
            //LOG.timeoutDuringShutdown();
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
