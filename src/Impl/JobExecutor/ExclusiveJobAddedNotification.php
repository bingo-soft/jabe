<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\TransactionListenerInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Management\Metrics;

class ExclusiveJobAddedNotification implements TransactionListenerInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobId;
    protected $jobExecutorContext;

    public function __construct(?string $jobId, JobExecutorContext $jobExecutorContext)
    {
        $this->jobId = $jobId;
        $this->jobExecutorContext = $jobExecutorContext;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        //LOG.debugAddingNewExclusiveJobToJobExecutorCOntext(jobId);
        $this->jobExecutorContext->addCurrentProcessorJobToQueue($this->jobId);
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
