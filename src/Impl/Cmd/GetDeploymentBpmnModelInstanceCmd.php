<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetDeploymentBpmnModelInstanceCmd implements CommandInterface
{
    protected $processDefinitionId;

    public function __construct(?string $processDefinitionId)
    {
        if (empty($processDefinitionId)) {
            throw new ProcessEngineException("The process definition id is mandatory, but '" . $processDefinitionId . "' has been provided.");
        }
        $this->processDefinitionId = $processDefinitionId;
    }

    public function __serialize(): array
    {
        return [
            'processDefinitionId' => $this->processDefinitionId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processDefinitionId = $data['processDefinitionId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
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

    public function isRetryable(): bool
    {
        return false;
    }
}
