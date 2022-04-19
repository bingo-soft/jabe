<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
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
