<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Variable\Variables;

class SubmitStartFormCmd implements CommandInterface
{
    protected $processDefinitionId;
    protected $businessKey;
    protected $variables;

    public function __construct(?string $processDefinitionId, ?string $businessKey, array $properties)
    {
        $this->processDefinitionId = $processDefinitionId;
        $this->businessKey = $businessKey;
        $this->variables = Variables::fromMap($properties);
    }

    public function __serialize(): array
    {
        return [
            'processDefinitionId' => $this->processDefinitionId,
            'variables' => serialize($this->variables),
            'businessKey' => $this->businessKey
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->variables = unserialize($data['variables']);
        $this->businessKey = $data['businessKey'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $deploymentCache = $processEngineConfiguration->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($this->processDefinitionId);
        EnsureUtil::ensureNotNull("No process definition found for id = '" . $this->processDefinitionId . "'", "processDefinition", $processDefinition);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateProcessInstance($processDefinition);
        }

        $processInstance = null;
        if ($this->businessKey !== null) {
            $processInstance = $processDefinition->createProcessInstance($this->businessKey);
        } else {
            $processInstance = $processDefinition->createProcessInstance();
        }

        $processInstance->startWithFormProperties($this->variables);

        return $processInstance;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
