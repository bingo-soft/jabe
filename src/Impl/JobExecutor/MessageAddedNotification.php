<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\TransactionListenerInterface;
use Jabe\Impl\Interceptor\CommandContext;

class MessageAddedNotification implements TransactionListenerInterface
{
    //private final JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobExecutor;

    //jobExecutor can be null for queued tasks
    public function __construct(?JobExecutor $jobExecutor)
    {
        $this->jobExecutor = $jobExecutor;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        //LOG.debugNotifyingJobExecutor("notifying job executor of new job");
        //$this->jobExecutor->jobWasAdded();
    }
}
