<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetDeploymentResourcesCmd implements CommandInterface
{
    protected $deploymentId;

    public function __construct(?string $deploymentId)
    {
        $this->deploymentId = $deploymentId;
    }

    public function __serialize(): array
    {
        return [
            'deploymentId' => $this->deploymentId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->deploymentId = $data['deploymentId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("deploymentId", "deploymentId", $this->deploymentId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadDeployment($this->deploymentId);
        }

        return Context::getCommandContext()
            ->getResourceManager()
            ->findResourcesByDeploymentId($this->deploymentId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
