<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
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
