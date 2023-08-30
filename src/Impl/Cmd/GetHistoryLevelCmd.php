<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetHistoryLevelCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadHistoryLevel");
        return Context::getProcessEngineConfiguration()->getHistoryLevel()->getId();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
