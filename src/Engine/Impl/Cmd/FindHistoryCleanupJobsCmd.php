<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\JobExecutor\HistoryCleanup\HistoryCleanupJobHandler;

class FindHistoryCleanupJobsCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getJobManager()->findJobsByHandlerType(HistoryCleanupJobHandler::TYPE);
    }
}
