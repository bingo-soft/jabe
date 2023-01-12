<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class UnregisterDeploymentCmd implements CommandInterface
{
    protected $deploymentIds = [];

    public function __construct(string | array $deployments)
    {
        $this->deploymentIds = is_string($deployments) ? [ $deployments ] : $deployments;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkUnregisterDeployment");
        $deployments = &Context::getProcessEngineConfiguration()->getRegisteredDeployments();
        foreach ($deployments as $key => $deployment) {
            if (in_array($deployment, $this->deploymentIds)) {
                unset($deployments[$key]);
            }
        }
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
