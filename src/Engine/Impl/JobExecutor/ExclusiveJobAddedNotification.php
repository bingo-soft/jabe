<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\TransactionListenerInterface;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Management\Metrics;

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

    public function execute(CommandContext $commandContext)
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
