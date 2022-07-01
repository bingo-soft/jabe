<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandInterface
};

class GetRegisteredDeploymentsCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadRegisteredDeployments");
        $registeredDeployments = Context::getProcessEngineConfiguration()->getRegisteredDeployments();
        return $registeredDeployments;
    }
}
