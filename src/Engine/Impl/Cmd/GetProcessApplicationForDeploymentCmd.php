<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetProcessApplicationForDeploymentCmd implements CommandInterface
{
    protected $deploymentId;

    public function __construct(string $deploymentId)
    {
        $this->deploymentId = $deploymentId;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadProcessApplicationForDeployment");

        $reference = Context::getProcessEngineConfiguration()
            ->getProcessApplicationManager()
            ->getProcessApplicationForDeployment($this->deploymentId);

        if ($reference != null) {
            return $reference->getName();
        } else {
            return null;
        }
    }
}
