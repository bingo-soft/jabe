<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Cfg\TransactionListenerInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;

class MessageAddedNotification implements TransactionListenerInterface
{
    //private final JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobExecutor;

    public function __construct(JobExecutor $jobExecutor)
    {
        $this->jobExecutor = $jobExecutor;
    }

    public function execute(CommandContext $commandContext): void
    {
        //LOG.debugNotifyingJobExecutor("notifying job executor of new job");
        $jobExecutor->jobWasAdded();
    }
}
