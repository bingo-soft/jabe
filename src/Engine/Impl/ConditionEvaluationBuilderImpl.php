<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Cmd\EvaluateStartConditionCmd;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\{
    ConditionEvaluationBuilderInterface,
    ProcessInstanceInterface
};
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Impl\VariableMapImpl;

class ConditionEvaluationBuilderImpl implements ConditionEvaluationBuilderInterface
{
    protected $commandExecutor;
    protected $businessKey;
    protected $processDefinitionId;

    protected $variables;

    protected $tenantId = null;
    protected $isTenantIdSet = false;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        EnsureUtil::ensureNotNull("commandExecutor", "commandExecutor", $commandExecutor);
        $this->variables = new VariableMapImpl();
        $this->commandExecutor = $commandExecutor;
    }

    public function getCommandExecutor(): CommandExecutorInterface
    {
        return $this->commandExecutor;
    }

    public function getBusinessKey(): string
    {
        return $this->businessKey;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getVariables(): VariableMapInterface
    {
        return $this->variables;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    protected function execute(CommandInterface $command)
    {
        return $this->commandExecutor->execute($command);
    }

    public function processInstanceBusinessKey(string $businessKey): ConditionEvaluationBuilderInterface
    {
        EnsureUtil::ensureNotNull("businessKey", "businessKey", $businessKey);
        $this->businessKey = $businessKey;
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): ConditionEvaluationBuilderInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function setVariable(string $variableName, $variableValue): ConditionEvaluationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $this->variables->put($variableName, $variableValue);
        return $this;
    }

    public function setVariables(array $variables): ConditionEvaluationBuilderInterface
    {
        EnsureUtil::ensureNotNull("variables", "variables", $variables);
        if (!empty($variables)) {
            $this->variables->putAll($variables);
        }
        return $this;
    }

    public function tenantId(string $tenantId): ConditionEvaluationBuilderInterface
    {
        EnsureUtil::ensureNotNull(
            "The tenant-id cannot be null. Use 'withoutTenantId()' if you want to evaluate conditional start event with a process definition which has no tenant-id.",
            "tenantId",
            $tenantId
        );

        $this->isTenantIdSet = true;
        $this->tenantId = $tenantId;
        return $this;
    }

    public function withoutTenantId(): ConditionEvaluationBuilderInterface
    {
        $this->isTenantIdSet = true;
        $this->tenantId = null;
        return $this;
    }

    public function evaluateStartConditions(): array
    {
        return $this->execute(new EvaluateStartConditionCmd($this));
    }
}
