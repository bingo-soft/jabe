<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetDeploymentResourceForIdCmd implements CommandInterface
{
    protected $deploymentId;
    protected $resourceId;

    public function __construct(?string $deploymentId, ?string $resourceId)
    {
        $this->deploymentId = $deploymentId;
        $this->resourceId = $resourceId;
    }

    public function __serialize(): array
    {
        return [
            'deploymentId' => $this->deploymentId,
            'resourceId' => $this->resourceId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->deploymentId = $data['deploymentId'];
        $this->resourceId = $data['resourceId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("deploymentId", "deploymentId", $this->deploymentId);
        EnsureUtil::ensureNotNull("resourceId", "resourceId", $this->resourceId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadDeployment($this->deploymentId);
        }

        $resource = $commandContext
            ->getResourceManager()
            ->findResourceByDeploymentIdAndResourceId($this->deploymentId, $this->resourceId);
            EnsureUtil::ensureNotNull("no resource found with id '" . $this->resourceId . "' in deployment '" . $this->deploymentId . "'", "resource", $resource);
        return $resource->getBytes();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
