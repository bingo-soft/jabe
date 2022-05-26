<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\TransactionListenerInterface;
use Jabe\Engine\Impl\Interceptor\CommandContext;

class MessageAddedNotification implements TransactionListenerInterface
{
    //private final JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobExecutor;

    public function __construct(JobExecutor $jobExecutor)
    {
        $this->jobExecutor = $jobExecutor;
    }

    public function execute(CommandContext $commandContext)
    {
        //LOG.debugNotifyingJobExecutor("notifying job executor of new job");
        $jobExecutor->jobWasAdded();
    }
}
