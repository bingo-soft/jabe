<?php

namespace Jabe\Engine\Impl\Runtime;

use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;
use Jabe\Engine\Impl\{
    ProcessEngineLogger,
    UpdateProcessInstancesSuspensionStateBuilderImpl
};
use Jabe\Engine\Impl\Cmd\{
    ActivateProcessInstanceCmd,
    CommandLogger,
    SuspendProcessInstanceCmd
};
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Runtime\{
    ProcessInstanceQueryInterface,
    UpdateProcessInstanceSuspensionStateBuilderInterface,
    UpdateProcessInstanceSuspensionStateSelectBuilderInterface,
    UpdateProcessInstanceSuspensionStateTenantBuilderInterface,
    UpdateProcessInstancesSuspensionStateBuilderInterface
};

class UpdateProcessInstanceSuspensionStateBuilderImpl implements UpdateProcessInstanceSuspensionStateBuilderInterface, UpdateProcessInstanceSuspensionStateSelectBuilderInterface, UpdateProcessInstanceSuspensionStateTenantBuilderInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $commandExecutor;

    protected $processInstanceId;

    protected $processDefinitionKey;
    protected $processDefinitionId;

    protected $processDefinitionTenantId;
    protected $isProcessDefinitionTenantIdSet = false;

    public function __construct(?CommandExecutorInterface $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function byProcessInstanceIds(array $processInstanceIds): UpdateProcessInstancesSuspensionStateBuilderInterface
    {
        return (new UpdateProcessInstancesSuspensionStateBuilderImpl($this->commandExecutor))->byProcessInstanceIds($processInstanceIds);
    }

    public function byHistoricProcessInstanceQuery(HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery): UpdateProcessInstancesSuspensionStateBuilderInterface
    {
        return (new UpdateProcessInstancesSuspensionStateBuilderImpl($this->commandExecutor))->byHistoricProcessInstanceQuery($historicProcessInstanceQuery);
    }

    public function byProcessInstanceId(string $processInstanceId): UpdateProcessInstanceSuspensionStateBuilderImpl
    {
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function byProcessDefinitionId(string $processDefinitionId): UpdateProcessInstanceSuspensionStateBuilderImpl
    {
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function byProcessDefinitionKey(string $processDefinitionKey): UpdateProcessInstanceSuspensionStateBuilderImpl
    {
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionWithoutTenantId(): UpdateProcessInstanceSuspensionStateBuilderImpl
    {
        $this->processDefinitionTenantId = null;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function processDefinitionTenantId(string $tenantId): UpdateProcessInstanceSuspensionStateBuilderImpl
    {
        $this->processDefinitionTenantId = $tenantId;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function activate(): void
    {
        $this->validateParameters();

        $command = new ActivateProcessInstanceCmd($this);
        $this->commandExecutor->execute($command);
    }

    public function suspend(): void
    {
        $this->validateParameters();

        $command = new SuspendProcessInstanceCmd($this);
        $this->commandExecutor->execute($command);
    }

    protected function validateParameters(): void
    {
        if ($this->processInstanceId == null && $this->processDefinitionId == null && $this->processDefinitionKey == null) {
            throw new \Exception("Need to specify either a process instance id, a process definition id or a process definition key.");
        }
        if ($this->isProcessDefinitionTenantIdSet && ($this->processInstanceId != null || $this->processDefinitionId != null)) {
            //throw LOG.exceptionUpdateSuspensionStateForTenantOnlyByProcessDefinitionKey();
        }
        if ($this->commandExecutor == null) {
            throw new \Exception("Command executor is undefined!");
        }
    }

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionTenantId(): ?string
    {
        return $this->processDefinitionTenantId;
    }

    public function isProcessDefinitionTenantIdSet(): bool
    {
        return $this->isProcessDefinitionTenantIdSet;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }
}
