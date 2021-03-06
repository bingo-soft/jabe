<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

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
