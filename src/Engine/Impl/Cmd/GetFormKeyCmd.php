<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetFormKeyCmd implements CommandInterface
{
    protected $taskDefinitionKey;
    protected $processDefinitionId;

    /**
     * Retrieves a task form key.
     */
    public function __construct(string $processDefinitionId, ?string $taskDefinitionKey = null)
    {
        $this->setProcessDefinitionId($processDefinitionId);
        if ($taskDefinitionKey !== null) {
            if (empty($taskDefinitionKey)) {
                throw new ProcessEngineException("The task definition key is mandatory, but '" . $taskDefinitionKey . "' has been provided.");
            }
            $this->taskDefinitionKey = $taskDefinitionKey;
        }
    }

    protected function setProcessDefinitionId(?string $processDefinitionId): void
    {
        if (empty($processDefinitionId)) {
            throw new ProcessEngineException("The process definition id is mandatory, but '" . $processDefinitionId . "' has been provided.");
        }
        $this->processDefinitionId = $processDefinitionId;
    }

    public function execute(CommandContext $commandContext)
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $deploymentCache = $processEngineConfiguration->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($this->processDefinitionId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessDefinition($processDefinition);
        }

        $formKeyExpression = null;

        if ($this->taskDefinitionKey === null) {
            $formDefinition = $processDefinition->getStartFormDefinition();
            $formKeyExpression = $formDefinition->getFormKey();
        } else {
            $taskDefinition = $processDefinition->getTaskDefinitions()[$this->taskDefinitionKey];
            $formKeyExpression = $taskDefinition->getFormKey();
        }

        $formKey = null;
        if ($formKeyExpression !== null) {
            $formKey = $formKeyExpression->getExpressionText();
        }
        return $formKey;
    }
}
