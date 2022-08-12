<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Container\{
    BaseRuntimeContainerDelegate,
    ExecutorServiceInterface,
    RuntimeContainerDelegateInterface
};
use Jabe\ProcessEngineException;
use Jabe\Impl\ProcessEngineImpl;

class RuntimeContainerJobExecutor extends JobExecutor
{
    protected function startExecutingJobs(): void
    {
        $runtimeContainerDelegate = $this->getRuntimeContainerDelegate();

        // schedule job acquisition
        if (!$runtimeContainerDelegate->getExecutorService()->schedule($this->acquireJobsRunnable, true)) {
            throw new ProcessEngineException("Could not schedule AcquireJobsRunnable for execution.");
        }
    }

    protected function stopExecutingJobs(): void
    {
      // nothing to do
    }

    public function executeJobs(array $jobIds, ProcessEngineImpl $processEngine): void
    {
        $runtimeContainerDelegate = $this->getRuntimeContainerDelegate();
        $executorService = $runtimeContainerDelegate->getExecutorService();

        $executeJobsRunnable = $this->getExecuteJobsRunnable($jobIds, $processEngine);

        // delegate job execution to runtime container
        if (!$executorService->schedule($executeJobsRunnable, false)) {
            $this->logRejectedExecution($processEngine, count($jobIds));
            $this->rejectedJobsHandler->jobsRejected($jobIds, $processEngine, $this);
        }
    }

    protected function getRuntimeContainerDelegate(): RuntimeContainerDelegateInterface
    {
        return BaseRuntimeContainerDelegate::instance();
    }

    public function getExecuteJobsRunnable(array $jobIds, ProcessEngineImpl $processEngine): RunnableInterface
    {
        $runtimeContainerDelegate = $this->getRuntimeContainerDelegate();
        $executorService = $runtimeContainerDelegate->getExecutorService();

        return $executorService->getExecuteJobsRunnable($jobIds, $processEngine);
    }
}
