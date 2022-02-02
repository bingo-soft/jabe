<?php

namespace BpmPlatform\Engine\Impl\Persistence\Deploy;

use BpmPlatform\Engine\Impl\Cmd\UnregisterDeploymentCmd;
use BpmPlatform\Engine\Impl\Interceptor\{
    AbstractCommand,
    CommandContext
};

class DeleteDeploymentListenerCmd extends AbstractCommand
{
    private $deploymentIds;

    public function __construct(array $deploymentIds)
    {
        $this->deploymentIds = $deploymentIds;
    }

    public function execute(CommandContext $commandContext)
    {
        $scope = $this;
        $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $cmd = new UnregisterDeploymentCmd($scope->deploymentIds);
            $cmd->execute($commandContext);
            return null;
        });
    }
}
