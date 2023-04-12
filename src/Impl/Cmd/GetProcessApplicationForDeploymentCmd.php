<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetProcessApplicationForDeploymentCmd implements CommandInterface
{
    protected $deploymentId;

    public function __construct(?string $deploymentId)
    {
        $this->deploymentId = $deploymentId;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadProcessApplicationForDeployment");

        $reference = Context::getProcessEngineConfiguration()
            ->getProcessApplicationManager()
            ->getProcessApplicationForDeployment($this->deploymentId);

        if ($reference !== null) {
            return $reference->getName();
        } else {
            return null;
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
