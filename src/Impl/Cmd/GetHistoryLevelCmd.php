<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetHistoryLevelCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkCamundaAdminOrPermission("checkReadHistoryLevel");
        return Context::getProcessEngineConfiguration()->getHistoryLevel()->getId();
    }
}
