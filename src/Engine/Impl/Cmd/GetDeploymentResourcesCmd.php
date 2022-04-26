<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetDeploymentResourcesCmd implements CommandInterface, \Serializable
{
    protected $deploymentId;

    public function __construct(string $deploymentId)
    {
        $this->deploymentId = $deploymentId;
    }

    public function serialize()
    {
        return json_encode([
            'deploymentId' => $this->deploymentId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->deploymentId = $json->deploymentId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("deploymentId", "deploymentId", $this->deploymentId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadDeployment($this->deploymentId);
        }

        return Context::getCommandContext()
            ->getResourceManager()
            ->findResourcesByDeploymentId($this->deploymentId);
    }
}
