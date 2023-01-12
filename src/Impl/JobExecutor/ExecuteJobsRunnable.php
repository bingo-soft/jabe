<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\{
    ProcessEngineImpl,
    ProcessEngineLogger
};
use Jabe\Impl\Cmd\{
    ExecuteJobsCmd,
    UnlockJobCmd
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandExecutorInterface,
    ProcessDataContext
};
use Concurrent\RunnableInterface;

class ExecuteJobsRunnable implements RunnableInterface
{
    //private static final JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobIds;
    protected $jobExecutor;
    protected $processEngine;

    public function __construct(array $jobIds, ProcessEngineImpl $processEngine)
    {
        $this->jobIds = $jobIds;
        $this->processEngine = $processEngine;
        $this->jobExecutor = $processEngine->getProcessEngineConfiguration()->getJobExecutor();
    }

    public function run(): void
    {
        $jobExecutorContext = new JobExecutorContext();

        $currentProcessorJobQueue = $jobExecutorContext->getCurrentProcessorJobQueue();
        $engineConfiguration = $this->processEngine->getProcessEngineConfiguration();
        $commandExecutor = $engineConfiguration->getCommandExecutorTxRequired();

        $currentProcessorJobQueue = array_merge($currentProcessorJobQueue, $this->jobIds);

        Context::setJobExecutorContext($jobExecutorContext);

        try {
            while (!empty($currentProcessorJobQueue)) {
                $nextJobId = array_shift($currentProcessorJobQueue);
                if ($this->jobExecutor->isActive()) {
                    $jobFailureCollector = new JobFailureCollector($nextJobId);
                    try {
                        $this->executeJob($nextJobId, $commandExecutor, $jobFailureCollector);
                    } catch (\Throwable $t) {
                        if (ProcessEngineLogger::shouldLogJobException($engineConfiguration, $jobFailureCollector->getJob())) {
                            ExecuteJobHelper::loggingHandler()->exceptionWhileExecutingJob($nextJobId, $t);
                        }
                    } finally {
                        /*
                        * clear MDC of potential leftovers from command execution
                        * that have not been cleared in Context#removeCommandInvocationContext()
                        * in case of exceptions in command execution
                        */
                        (new ProcessDataContext($engineConfiguration))->clearMdc();
                    }
                } else {
                    try {
                        $this->unlockJob($nextJobId, $commandExecutor);
                    } catch (\Throwable $t) {
                        //LOG.exceptionWhileUnlockingJob(nextJobId, t);
                    }
                }
            }

            // if there were only exclusive jobs then the job executor
            // does a backoff. In order to avoid too much waiting time
            // we need to tell him to check once more if there were any jobs added.
            $this->jobExecutor->jobWasAdded();
        } finally {
            Context::removeJobExecutorContext();
        }
    }

    protected function executeJob(?string $nextJobId, CommandExecutorInterface $commandExecutor, JobFailureCollector $jobFailureCollector): void
    {
        ExecuteJobHelper::executeJob($nextJobId, $commandExecutor, $jobFailureCollector, new ExecuteJobsCmd($nextJobId, $jobFailureCollector), $this->processEngine->getProcessEngineConfiguration());
    }

    protected function unlockJob(?string $nextJobId, CommandExecutorInterface $commandExecutor): void
    {
        $commandExecutor->execute(new UnlockJobCmd($nextJobId));
    }
}
