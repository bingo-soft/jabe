<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\JobEntity;
use Jabe\Management\Metrics;

class FailedJobListener implements CommandInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;
    protected $commandExecutor;
    protected $jobFailureCollector;
    protected int $countRetries = 0;
    protected int $totalRetries = ProcessEngineConfigurationImpl::DEFAULT_FAILED_JOB_LISTENER_MAX_RETRIES;

    public function __construct(CommandExecutorInterface $commandExecutor, JobFailureCollector $jobFailureCollector)
    {
        $this->commandExecutor = $commandExecutor;
        $this->jobFailureCollector = $jobFailureCollector;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->initTotalRetries($commandContext);

        $this->logJobFailure($commandContext);

        $failedJobCommandFactory = $commandContext->getFailedJobCommandFactory();
        $jobId = $this->jobFailureCollector->getJobId();
        $cmd = $failedJobCommandFactory->getCommand($jobId, $this->jobFailureCollector->getFailure());
        $this->commandExecutor->execute(new FailedJobListenerCmd($jobId, $cmd, $this));

        return null;
    }

    private function initTotalRetries(CommandContext $commandContext): void
    {
        $this->totalRetries = $commandContext->getProcessEngineConfiguration()->getFailedJobListenerMaxRetries();
    }

    public function getJobFailureCollector(): JobFailureCollector
    {
        return $this->jobFailureCollector;
    }

    public function fireHistoricJobFailedEvt(JobEntity $job): void
    {
        $commandContext = Context::getCommandContext();

        // the given job failed and a rollback happened,
        // that's why we have to increment the job
        // sequence counter once again
        $job->incrementSequenceCounter();

        $commandContext
                ->getHistoricJobLogManager()
                ->fireJobFailedEvent($job, $this->jobFailureCollector->getFailure());
    }

    protected function logJobFailure(CommandContext $commandContext): void
    {
        if ($commandContext->getProcessEngineConfiguration()->isMetricsEnabled()) {
            $commandContext->getProcessEngineConfiguration()
                    ->getMetricsRegistry()
                    ->markOccurrence(Metrics::JOB_FAILED);
        }
    }

    public function incrementCountRetries(): void
    {
        $this->countRetries += 1;
    }

    public function getRetriesLeft(): int
    {
        return max([0, $this->totalRetries - $this->countRetries]);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
