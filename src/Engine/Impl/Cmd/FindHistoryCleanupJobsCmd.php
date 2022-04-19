<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\JobExecutor\HistoryCleanup\HistoryCleanupJobHandler;
use BpmPlatform\Engine\Runtime\JobInterface;

class FindHistoryCleanupJobsCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getJobManager()->findJobsByHandlerType(HistoryCleanupJobHandler::TYPE);
    }
}
