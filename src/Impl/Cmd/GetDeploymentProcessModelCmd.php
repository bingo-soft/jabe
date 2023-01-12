<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetDeploymentProcessModelCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionId;

    public function __construct(?string $processDefinitionId)
    {
        if (empty($processDefinitionId)) {
            throw new ProcessEngineException("The process definition id is mandatory, but '" . $processDefinitionId . "' has been provided.");
        }
        $this->processDefinitionId = $processDefinitionId;
    }

    public function serialize()
    {
        return json_encode([
            'processDefinitionId' => $this->processDefinitionId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processDefinitionId = $json->processDefinitionId;
    }

    public function execute(CommandContext $commandContext)
    {
        $processDefinition = Context::getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($this->processDefinitionId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessDefinition($processDefinition);
        }

        $deploymentId = $processDefinition->getDeploymentId();
        $resourceName = $processDefinition->getResourceName();

        if ($resourceName === null) {
            return null;
        } else {
            $processDiagramStream = $commandContext->runWithoutAuthorization(function () use ($commandContext, $deploymentId, $resourceName) {
                $cmd = new GetDeploymentResourceCmd($deploymentId, $resourceName);
                return $cmd->execute($commandContext);
            });

            return $processDiagramStream;
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
