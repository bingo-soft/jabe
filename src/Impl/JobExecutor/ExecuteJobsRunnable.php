<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\ProcessEngineConfiguration;
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
use Concurrent\{
    ExecutorServiceInterface,
    RunnableInterface,
    ThreadInterface
};

class ExecuteJobsRunnable implements \Serializable, RunnableInterface
{
    //private static final JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobIds;
    protected $jobExecutor;
    protected $processEngine;

    protected static $loadedProcessEngineConfiguration;
    protected static $loadedProcessEngine;

    public function __construct(array $jobIds, ProcessEngineImpl $processEngine)
    {
        $this->jobIds = $jobIds;
        $this->processEngine = $processEngine;
        $this->jobExecutor = $processEngine->getProcessEngineConfiguration()->getJobExecutor();
    }

    public function serialize()
    {
        return json_encode([
            'jobIds' => $this->jobIds,
            'resource' => $this->processEngine->getProcessEngineConfiguration()->getResource()
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->jobIds = $json->jobIds;
        if (self::$loadedProcessEngineConfiguration === null) {
            self::$loadedProcessEngineConfiguration = ProcessEngineConfiguration::createProcessEngineConfigurationFromResource($json->resource);
            self::$loadedProcessEngine = self::$loadedProcessEngineConfiguration->buildProcessEngine();
            $this->processEngine = self::$loadedProcessEngine;
            $this->jobExecutor = self::$loadedProcessEngineConfiguration->getJobExecutor();
        }
        $this->processEngine = self::$loadedProcessEngine;
        $this->jobExecutor = self::$loadedProcessEngineConfiguration->getJobExecutor();
    }

    public function run(ThreadInterface $process, ...$args): void
    {
        $jobExecutorContext = new JobExecutorContext();

        $currentProcessorJobQueue = $jobExecutorContext->getCurrentProcessorJobQueue();
        $engineConfiguration = $this->processEngine->getProcessEngineConfiguration();
        $commandExecutor = $engineConfiguration->getCommandExecutorTxRequired();
        //$currentProcessorJobQueue = array_merge($currentProcessorJobQueue, $this->jobIds);
        Context::setJobExecutorContext($jobExecutorContext);
        try {
            while (!empty($this->jobIds)) {
                $nextJobId = array_shift($this->jobIds);
                if ($args[0]->get()) {
                    $jobFailureCollector = new JobFailureCollector($nextJobId);
                    try {
                        $this->executeJob($nextJobId, $commandExecutor, $jobFailureCollector);
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
            //$jobExecutor->jobWasAdded();
            $args[1]->set(1);
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
