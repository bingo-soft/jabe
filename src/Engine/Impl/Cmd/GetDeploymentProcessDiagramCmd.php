<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetDeploymentProcessDiagramCmd implements CommandInterface, \Serializable
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
        $resourceName = $processDefinition->getDiagramResourceName();

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
}
