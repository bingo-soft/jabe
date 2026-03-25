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
use Jabe\Impl\Util\ClockUtil;
use Concurrent\{
    ExecutorServiceInterface,
    RunnableInterface,
    ThreadInterface
};

class ExecuteJobsRunnable implements RunnableInterface
{
    //private static final JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;
    protected $jobIds;
    protected $resource;
    protected $processEngine;

    protected static $loadedProcessEngineConfiguration;
    protected static $loadedProcessEngine;
    protected bool $queued = false;
    protected $jobExecutionBootstrap;
    protected bool $jobExecutionBootstrapInvoked = false;

    public function __construct(array $jobIds, ProcessEngineImpl $processEngine, ?callable $jobExecutionBootstrap = null)
    {
        $this->jobIds = $jobIds;
        $this->processEngine = $processEngine;
        $this->jobExecutionBootstrap = $jobExecutionBootstrap;
        //$this->jobExecutor = $processEngine->getProcessEngineConfiguration()->getJobExecutor();
    }

    public function __serialize(): array
    {
        return [
            'jobIds' => $this->jobIds,
            'resource' => $this->processEngine->getProcessEngineConfiguration()->getResource(),
            'jobExecutionBootstrap' => $this->isSerializableBootstrap($this->jobExecutionBootstrap)
                ? $this->jobExecutionBootstrap
                : null
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->queued = true;
        $this->jobIds = $data['jobIds'];
        $this->resource = $data['resource'];
        $this->jobExecutionBootstrap = $data['jobExecutionBootstrap'] ?? null;
    }

    public function run(ThreadInterface $process = null, ...$args): void
    {
        $this->ensureProcessEngineConfigurationExists(...$args);

        $this->invokeJobExecutionBootstrap();

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
                        $this->executeJob($nextJobId, $commandExecutor, $jobFailureCollector, ...$args);
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

    private function ensureProcessEngineConfigurationExists(...$args): void
    {
        if ($this->queued) {
            if (self::$loadedProcessEngineConfiguration === null) {
                self::$loadedProcessEngineConfiguration = ProcessEngineConfiguration::createProcessEngineConfigurationFromResource($this->resource);
                self::$loadedProcessEngineConfiguration->setJobExecutorState(...$args);
                self::$loadedProcessEngine = self::$loadedProcessEngineConfiguration->buildProcessEngine(true);
            }
        }
        if ($this->processEngine === null && self::$loadedProcessEngine !== null) {
            $this->processEngine = self::$loadedProcessEngine;
        }
    }

    private function invokeJobExecutionBootstrap(): void
    {
        if ($this->jobExecutionBootstrapInvoked) {
            return;
        }

        try {
            if (is_callable($this->jobExecutionBootstrap)) {
                $this->callBootstrap($this->jobExecutionBootstrap);
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, sprintf("[%s] Job execution bootstrap failed: %s\n", date("d-m-Y H:i:s"), $e->getMessage()));
        }

        $this->jobExecutionBootstrapInvoked = true;
    }

    private function callBootstrap(callable $bootstrap): void
    {
        if (is_array($bootstrap) && count($bootstrap) === 2) {
            $reflection = new \ReflectionMethod($bootstrap[0], $bootstrap[1]);
        } else {
            $reflection = new \ReflectionFunction(\Closure::fromCallable($bootstrap));
        }

        if ($reflection->getNumberOfParameters() > 0) {
            $bootstrap($this->processEngine);
            return;
        }

        $bootstrap();
    }

    private function isSerializableBootstrap($bootstrap): bool
    {
        if (!is_callable($bootstrap)) {
            return false;
        }

        return !($bootstrap instanceof \Closure);
    }

    protected function executeJob(?string $nextJobId, CommandExecutorInterface $commandExecutor, JobFailureCollector $jobFailureCollector, ...$args): void
    {
        ExecuteJobHelper::executeJob($nextJobId, $commandExecutor, $jobFailureCollector, new ExecuteJobsCmd($nextJobId, $jobFailureCollector), $this->processEngine->getProcessEngineConfiguration(), ...$args);
    }

    protected function unlockJob(?string $nextJobId, CommandExecutorInterface $commandExecutor): void
    {
        $commandExecutor->execute(new UnlockJobCmd($nextJobId));
    }
}
