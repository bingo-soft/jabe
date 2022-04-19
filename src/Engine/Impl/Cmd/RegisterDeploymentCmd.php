<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class RegisterDeploymentCmd implements CommandInterface
{
    protected $deploymentId;

    public function __construct(string $deploymentId)
    {
        $this->deploymentId = $deploymentId;
    }

    public function execute(CommandContext $commandContext)
    {
        $deployment = $commandContext->getDeploymentManager()->findDeploymentById($this->deploymentId);

        EnsureUtil::ensureNotNull("Deployment " . $this->deploymentId . " does not exist", "deployment", $deployment);

        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkRegisterDeployment");

        Context::getProcessEngineConfiguration()->registerDeployment($this->deploymentId);
        return null;
    }
}
