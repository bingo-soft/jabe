<?php

namespace Jabe\Impl\Persistence\Deploy;

use Jabe\Application\ProcessApplicationReferenceInterface;
use Jabe\Impl\Cmd\{
    RegisterDeploymentCmd,
    RegisterProcessApplicationCmd
};
use Jabe\Impl\Interceptor\{
    AbstractCommand,
    CommandContext
};

class DeleteDeploymentFailCmd extends AbstractCommand
{
    private $deploymentId;
    private $processApplicationReference;

    public function __construct(?string $deploymentId, ?ProcessApplicationReferenceInterface $processApplicationReference)
    {
        $this->deploymentId = $deploymentId;
        $this->processApplicationReference = $processApplicationReference;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $scope = $this;
        $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $cmd = new RegisterDeploymentCmd($scope->deploymentId);
            $cmd->execute($commandContext);
        });
        if ($this->processApplicationReference !== null) {
            $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
                $cmd = new RegisterProcessApplicationCmd($scope->deploymentId, $scope->processApplicationReference);
                $cmd->execute($commandContext);
            });
        }
        return null;
    }
}
