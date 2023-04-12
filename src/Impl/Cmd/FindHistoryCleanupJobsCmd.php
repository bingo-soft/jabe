<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\JobExecutor\HistoryCleanup\HistoryCleanupJobHandler;

class FindHistoryCleanupJobsCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext->getJobManager()->findJobsByHandlerType(HistoryCleanupJobHandler::TYPE);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
