<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface
};

class GetRegisteredDeploymentsCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadRegisteredDeployments");
        $registeredDeployments = Context::getProcessEngineConfiguration()->getRegisteredDeployments();
        return $registeredDeployments;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
