<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Cfg\TransactionListenerInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Management\Metrics;

class ExclusiveJobAddedNotification implements TransactionListenerInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobId;
    protected $jobExecutorContext;

    public function __construct(string $jobId, JobExecutorContext $jobExecutorContext)
    {
        $this->jobId = $jobId;
        $this->jobExecutorContext = $jobExecutorContext;
    }

    public function execute(CommandContext $commandContext): void
    {
        //LOG.debugAddingNewExclusiveJobToJobExecutorCOntext(jobId);
        $this->jobExecutorContext->addCurrentProcessorJobToQueue($jobId);
        $this->logExclusiveJobAdded($commandContext);
    }

    protected function logExclusiveJobAdded(CommandContext $commandContext): void
    {
        if ($commandContext->getProcessEngineConfiguration()->isMetricsEnabled()) {
            $commandContext->getProcessEngineConfiguration()
            ->getMetricsRegistry()
            ->markOccurrence(Metrics::JOB_LOCKED_EXCLUSIVE);
        }
    }
}
