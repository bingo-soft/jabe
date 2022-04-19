<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class GetDeploymentBpmnModelInstanceCmd implements CommandInterface, \Serializable
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
        $configuration = Context::getProcessEngineConfiguration();
        $deploymentCache = $configuration->getDeploymentCache();

        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($this->processDefinitionId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessDefinition($processDefinition);
        }

        $modelInstance = $deploymentCache->findBpmnModelInstanceForProcessDefinition($this->processDefinitionId);

        EnsureUtil::ensureNotNull("no BPMN model instance found for process definition id " . $this->processDefinitionId, "modelInstance", $modelInstance);
        return $modelInstance;
    }
}
