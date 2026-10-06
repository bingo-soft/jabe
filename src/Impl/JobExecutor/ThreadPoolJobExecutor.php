<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineImpl;
use Jabe\Impl\Cmd\{SetJobsRetriesCmd, UnlockJobCmd};
use Concurrent\ExecutorServiceInterface;

class ThreadPoolJobExecutor extends JobExecutor
{
    private const SHARED_QUEUE_TASK_BYTES = 8192;
    protected $threadPoolExecutor;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    protected function startExecutingJobs(...$args): void
    {
        $this->startJobAcquisitionThread(...$args);
    }

    protected function stopExecutingJobs(): void
    {
        $this->stopJobAcquisitionThread();
    }

    public function executeJobs(array $jobIds, ?ProcessEngineImpl $processEngine = null, ...$args): void
    {
        try {
            $runnable = $this->getExecuteJobsRunnable($jobIds, $processEngine);
            $serialized = serialize($runnable);
            if (strlen($serialized) >= self::SHARED_QUEUE_TASK_BYTES) {
                throw new \LengthException('Serialized worker task exceeds shared queue slot size');
            }
            $this->threadPoolExecutor->execute($runnable);
        } catch (\LengthException $e) {
            // Exclusive batches are indivisible. Mark every member as a controlled
            // failed job before unlocking them, preventing an acquisition spin loop.
            $this->failOversizedJobs($jobIds, $processEngine);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'Worker queue is full or executor is unavailable') {
                throw $e;
            }
            $this->logRejectedExecution($processEngine, count($jobIds));
            $this->rejectedJobsHandler->jobsRejected($jobIds, $processEngine, $this);
        }
    }

    private function failOversizedJobs(array $jobIds, ?ProcessEngineImpl $processEngine): void
    {
        if ($processEngine === null) {
            throw new \LogicException('Process engine is required to unlock rejected jobs');
        }

        $commandExecutor = $processEngine->getProcessEngineConfiguration()->getCommandExecutorTxRequired();
        $commandExecutor->execute(new SetJobsRetriesCmd($jobIds, 0));
        foreach ($jobIds as $jobId) {
            try {
                $commandExecutor->execute(new UnlockJobCmd((string) $jobId));
            } catch (\Throwable $unlockException) {
                throw new \RuntimeException(
                    sprintf('Unable to unlock oversized job batch member %s', $jobId),
                    0,
                    $unlockException
                );
            }
        }
    }

    // getters / setters
    public function getThreadPoolExecutor(): ExecutorServiceInterface
    {
        return $this->threadPoolExecutor;
    }

    public function setThreadPoolExecutor(ExecutorServiceInterface $threadPoolExecutor): void
    {
        $this->threadPoolExecutor = $threadPoolExecutor;
    }
}
