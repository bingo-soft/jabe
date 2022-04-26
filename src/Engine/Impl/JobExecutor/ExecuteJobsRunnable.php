<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\ProcessEngine;
use Jabe\Engine\Impl\{
    ProcessEngineImpl,
    ProcessEngineLogger
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Cmd\{
    ExecuteJobsCmd,
    UnlockJobCmd
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandExecutorInterface,
    ProcessDataContext
};
use Jabe\Engine\Impl\Util\ClassLoaderUtil;

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
        $engineConfiguration = $processEngine->getProcessEngineConfiguration();
        $commandExecutor = $engineConfiguration->getCommandExecutorTxRequired();

        $currentProcessorJobQueue = array_merge($currentProcessorJobQueue, $jobIds);

        Context::setJobExecutorContext($jobExecutorContext);

        $classLoaderBeforeExecution = $this->switchClassLoader();

        try {
            while (!empty($currentProcessorJobQueue)) {
                $nextJobId = array_shift($currentProcessorJobQueue);
                if ($jobExecutor->isActive()) {
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
            $jobExecutor->jobWasAdded();
        } finally {
            Context::removeJobExecutorContext();
            ClassLoaderUtil::setContextClassloader($classLoaderBeforeExecution);
        }
    }

    /**
     * Note: this is a hook to be overridden by
     * org.camunda.bpm.container.impl.threading.ra.inflow.JcaInflowExecuteJobsRunnable.executeJob(String, CommandExecutor)
     */
    protected function executeJob(string $nextJobId, CommandExecutorInterface $commandExecutor, JobFailureCollector $jobFailureCollector): void
    {
        ExecuteJobHelper::executeJob($nextJobId, $commandExecutor, $jobFailureCollector, new ExecuteJobsCmd($nextJobId, $jobFailureCollector), $processEngine->getProcessEngineConfiguration());
    }

    protected function unlockJob(string $nextJobId, CommandExecutorInterface $commandExecutor): void
    {
        $commandExecutor->execute(new UnlockJobCmd($nextJobId));
    }

    /**
     * Switch the context classloader to the ProcessEngine's
     * to assure the loading of the engine classes during job execution<br>
     *
     * @return the classloader before the switch to return it back after the job execution
     */
    protected function switchClassLoader(): string
    {
        return ClassLoaderUtil::switchToProcessEngineClassloader();
    }
}
