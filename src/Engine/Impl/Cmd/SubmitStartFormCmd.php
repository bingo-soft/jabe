<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;
use BpmPlatform\Engine\Variable\Variables;

class SubmitStartFormCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionId;
    protected $businessKey;
    protected $variables;

    public function __construct(string $processDefinitionId, ?string $businessKey, array $properties)
    {
        $this->processDefinitionId = $processDefinitionId;
        $this->businessKey = $businessKey;
        $this->variables = Variables::fromMap($properties);
    }

    public function serialize()
    {
        return json_encode([
            'processDefinitionId' => $this->processDefinitionId,
            'variables' => serialize($this->variables),
            'businessKey' => $this->businessKey
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processDefinitionId = $json->processDefinitionId;
        $this->variables = unserialize($json->variables);
        $this->businessKey = $json->businessKey;
    }

    public function execute(CommandContext $commandContext)
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $deploymentCache = $processEngineConfiguration->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($this->processDefinitionId);
        EnsureUtil::ensureNotNull("No process definition found for id = '" . $this->processDefinitionId . "'", "processDefinition", $processDefinition);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateProcessInstance($processDefinition);
        }

        $processInstance = null;
        if ($this->businessKey != null) {
            $processInstance = $processDefinition->createProcessInstance($this->businessKey);
        } else {
            $processInstance = $processDefinition->createProcessInstance();
        }

        $processInstance->startWithFormProperties($this->variables);

        return $processInstance;
    }
}
