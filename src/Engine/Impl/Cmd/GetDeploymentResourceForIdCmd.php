<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetDeploymentResourceForIdCmd implements CommandInterface, \Serializable
{
    protected $deploymentId;
    protected $resourceId;

    public function __construct(string $deploymentId, string $resourceId)
    {
        $this->deploymentId = $deploymentId;
        $this->resourceId = $resourceId;
    }

    public function serialize()
    {
        return json_encode([
            'deploymentId' => $this->deploymentId,
            'resourceId' => $this->resourceId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->deploymentId = $json->deploymentId;
        $this->resourceId = $json->resourceId;
    }

    public function execute(CommandContext $commandContext)
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
}
