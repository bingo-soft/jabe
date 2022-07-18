<?php

namespace Jabe\Engine\Impl\Repository;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\{
    ActivateProcessDefinitionCmd,
    CommandLogger,
    SuspendProcessDefinitionCmd
};
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Repository\{
    UpdateProcessDefinitionSuspensionStateBuilderInterface,
    UpdateProcessDefinitionSuspensionStateSelectBuilderInterface,
    UpdateProcessDefinitionSuspensionStateTenantBuilderInterface
};

class UpdateProcessDefinitionSuspensionStateBuilderImpl implements UpdateProcessDefinitionSuspensionStateBuilderInterface, UpdateProcessDefinitionSuspensionStateSelectBuilderInterface, UpdateProcessDefinitionSuspensionStateTenantBuilderInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $commandExecutor;

    protected $processDefinitionKey;
    protected $processDefinitionId;

    protected $includeProcessInstances = false;
    protected $executionDate;

    protected $processDefinitionTenantId;
    protected $isTenantIdSet = false;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function byProcessDefinitionId(string $processDefinitionId): UpdateProcessDefinitionSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function byProcessDefinitionKey(string $processDefinitionKey): UpdateProcessDefinitionSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("processDefinitionKey", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function includeProcessInstances(bool $includeProcessInstance): UpdateProcessDefinitionSuspensionStateBuilderImpl
    {
        $this->includeProcessInstances = $includeProcessInstance;
        return $this;
    }

    public function executionDate(string $date): UpdateProcessDefinitionSuspensionStateBuilderImpl
    {
        $this->executionDate = $date;
        return $this;
    }

    public function processDefinitionWithoutTenantId(): UpdateProcessDefinitionSuspensionStateBuilderImpl
    {
        $this->processDefinitionTenantId = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function processDefinitionTenantId(string $tenantId): UpdateProcessDefinitionSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $tenantId);

        $this->processDefinitionTenantId = $tenantId;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function activate(): void
    {
        $this->validateParameters();

        $command = new ActivateProcessDefinitionCmd($this);
        $this->commandExecutor->execute($command);
    }

    public function suspend(): void
    {
        $this->validateParameters();

        $command = new SuspendProcessDefinitionCmd($this);
        $this->commandExecutor->execute($command);
    }

    protected function validateParameters(): void
    {
        EnsureUtil::ensureOnlyOneNotNull("Need to specify either a process instance id or a process definition key.", $this->processDefinitionId, $this->processDefinitionKey);

        if ($this->processDefinitionId !== null && $this->isTenantIdSet) {
            //throw LOG.exceptionUpdateSuspensionStateForTenantOnlyByProcessDefinitionKey();
            throw new \Exception("exceptionUpdateSuspensionStateForTenantOnlyByProcessDefinitionKey");
        }

        EnsureUtil::ensureNotNull("commandExecutor", "commandExecutor", $this->commandExecutor);
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function isIncludeProcessInstances(): bool
    {
        return $this->includeProcessInstances;
    }

    public function getExecutionDate(): string
    {
        return $this->executionDate;
    }

    public function getProcessDefinitionTenantId(): string
    {
        return $this->processDefinitionTenantId;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }
}
