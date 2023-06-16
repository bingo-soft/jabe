<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetDeploymentResourceCmd implements CommandInterface
{
    protected $deploymentId;
    protected $resourceName;

    public function __construct(?string $deploymentId, ?string $resourceName)
    {
        $this->deploymentId = $deploymentId;
        $this->resourceName = $resourceName;
    }

    public function __serialize(): array
    {
        return [
            'deploymentId' => $this->deploymentId,
            'resourceName' => $this->resourceName
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->deploymentId = $data['deploymentId'];
        $this->resourceName = $data['resourceName'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("deploymentId", "deploymentId", $this->deploymentId);
        EnsureUtil::ensureNotNull("resourceName", "resourceName", $this->resourceName);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadDeployment($this->deploymentId);
        }

        $resource = $commandContext
            ->getResourceManager()
            ->findResourceByDeploymentIdAndResourceName($this->deploymentId, $this->resourceName);
        EnsureUtil::ensureNotNull("no resource found with name '" . $this->resourceName . "' in deployment '" . $this->deploymentId . "'", "resource", $resource);
        return $resource->getBytes();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
