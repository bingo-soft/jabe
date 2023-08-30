<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetStartFormCmd implements CommandInterface
{
    protected $processDefinitionId;

    public function __construct(?string $processDefinitionId)
    {
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
