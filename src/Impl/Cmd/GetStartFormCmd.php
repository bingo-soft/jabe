<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetStartFormCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionId;

    public function __construct(?string $processDefinitionId)
    {
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
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $deploymentCache = $processEngineConfiguration->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($this->processDefinitionId);
        EnsureUtil::ensureNotNull("No process definition found for id '" . $this->processDefinitionId . "'", "processDefinition", $processDefinition);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessDefinition($processDefinition);
        }

        $startFormHandler = $processDefinition->getStartFormHandler();
        EnsureUtil::ensureNotNull("No startFormHandler defined in process '" . $this->processDefinitionId . "'", "startFormHandler", $startFormHandler);

        return $startFormHandler->createStartFormData($processDefinition);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
