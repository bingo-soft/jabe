<?php

namespace Jabe\Engine\Impl\ExternalTask;

use Jabe\Engine\ExternalTask\{
    ExternalTaskQueryBuilderInterface,
    LockedExternalTaskInterface
};
use Jabe\Engine\Impl\Cmd\FetchExternalTasksCmd;
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;

class ExternalTaskQueryTopicBuilderImpl implements ExternalTaskQueryTopicBuilderInterface
{
    protected $commandExecutor;

    protected $workerId;
    protected $maxTasks;
    /**
     * Indicates that priority is enabled.
     */
    protected $usePriority;

    protected $instructions;

    protected $currentInstruction;

    public function __construct(CommandExecutorInterface $commandExecutor, string $workerId, int $maxTasks, bool $usePriority)
    {
        $this->commandExecutor = $commandExecutor;
        $this->workerId = $workerId;
        $this->maxTasks = $maxTasks;
        $this->usePriority = $usePriority;
        $this->instructions = [];
    }

    public function execute(): array
    {
        $this->submitCurrentInstruction();
        return $this->commandExecutor->execute(new FetchExternalTasksCmd($this->workerId, $this->maxTasks, $this->instructions, $this->usePriority));
    }

    public function topic(string $topicName, int $lockDuration): ExternalTaskQueryTopicBuilderInterface
    {
        $this->submitCurrentInstruction();
        $this->currentInstruction = new TopicFetchInstruction($topicName, $lockDuration);
        return $this;
    }

    public function variables(?array $variables): ExternalTaskQueryTopicBuilderInterface
    {
        // don't use plain Arrays.asList since this returns an instance of a different list class
        // that is private and may mess mybatis queries up
        if (empty($variables)) {
            $this->currentInstruction->setVariablesToFetch($variables);
        }
        return $this;
    }

    public function processInstanceVariableEquals($one, $two = null): ExternalTaskQueryTopicBuilderInterface
    {
        if (is_array($one)) {
            $this->currentInstruction->setFilterVariables($one);
        } elseif (is_string($one) && is_string($two)) {
            $this->currentInstruction->addFilterVariable($one, $two);
        }
        return $this;
    }

    public function businessKey(string $businessKey): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setBusinessKey($businessKey);
        return $this;
    }

    public function processDefinitionId($processDefinitionId): ExternalTaskQueryTopicBuilderInterface
    {
        if (is_array($processDefinitionId)) {
            $this->currentInstruction->setProcessDefinitionIds($processDefinitionId);
        } elseif (is_string($processDefinitionId)) {
            $this->currentInstruction->setProcessDefinitionId($processDefinitionId);
        }
        return $this;
    }

    public function processDefinitionKey(string $processDefinitionKey): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setProcessDefinitionKey($processDefinitionKey);
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setProcessDefinitionKeys($processDefinitionKeys);
        return $this;
    }

    public function processDefinitionVersionTag(string $processDefinitionVersionTag): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setProcessDefinitionVersionTag($processDefinitionVersionTag);
        return $this;
    }

    public function withoutTenantId(): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setTenantIds(null);
        return $this;
    }

    public function tenantIdIn(array $tenantIds): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setTenantIds($tenantIds);
        return $this;
    }

    protected function submitCurrentInstruction(): void
    {
        if ($this->currentInstruction !== null) {
            $this->instructions[$this->currentInstruction->getTopicName()] = $this->currentInstruction;
        }
    }

    public function enableCustomObjectDeserialization(): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setDeserializeVariables(true);
        return $this;
    }

    public function localVariables(): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setLocalVariables(true);
        return $this;
    }

    public function includeExtensionProperties(): ExternalTaskQueryTopicBuilderInterface
    {
        $this->currentInstruction->setIncludeExtensionProperties(true);
        return $this;
    }
}
