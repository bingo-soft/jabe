<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetDeploymentResourceCmd implements CommandInterface, \Serializable
{
    protected $deploymentId;
    protected $resourceName;

    public function __construct(string $deploymentId, string $resourceName)
    {
        $this->deploymentId = $deploymentId;
        $this->resourceName = $resourceName;
    }

    public function serialize()
    {
        return json_encode([
            'deploymentId' => $this->deploymentId,
            'resourceName' => $this->resourceName
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->deploymentId = $json->deploymentId;
        $this->resourceName = $json->resourceName;
    }

    public function execute(CommandContext $commandContext)
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
}
