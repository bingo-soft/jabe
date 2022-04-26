<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
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
