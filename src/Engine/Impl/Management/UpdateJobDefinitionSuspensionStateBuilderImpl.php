<?php

namespace Jabe\Engine\Impl\Management;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\{
    ActivateJobDefinitionCmd,
    CommandLogger,
    SuspendJobDefinitionCmd
};
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Management\{
    UpdateJobDefinitionSuspensionStateBuilderInterface,
    UpdateJobDefinitionSuspensionStateSelectBuilderInterface,
    UpdateJobDefinitionSuspensionStateTenantBuilderInterface
};

class UpdateJobDefinitionSuspensionStateBuilderImpl implements UpdateJobDefinitionSuspensionStateBuilderInterface, UpdateJobDefinitionSuspensionStateSelectBuilderInterface, UpdateJobDefinitionSuspensionStateTenantBuilderInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $commandExecutor;

    protected $jobDefinitionId;

    protected $processDefinitionKey;
    protected $processDefinitionId;

    protected $processDefinitionTenantId;
    protected $isProcessDefinitionTenantIdSet = false;

    protected $includeJobs = false;
    protected $executionDate;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function byJobDefinitionId(string $jobDefinitionId): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("jobDefinitionId", "jobDefinitionId", $jobDefinitionId);
        $this->jobDefinitionId = $jobDefinitionId;
        return $this;
    }

    public function byProcessDefinitionId(string $processDefinitionId): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function byProcessDefinitionKey(string $processDefinitionKey): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("processDefinitionKey", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionWithoutTenantId(): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        $this->processDefinitionTenantId = null;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function processDefinitionTenantId(string $tenantId): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $tenantId);
        $this->processDefinitionTenantId = $tenantId;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function includeJobs(bool $includeJobs): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        $this->includeJobs = $includeJobs;
        return $this;
    }

    public function executionDate(string $executionDate): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        $this->executionDate = $executionDate;
        return $this;
    }

    public function activate(): void
    {
        $this->validateParameters();
        $command = new ActivateJobDefinitionCmd($this);
        $this->commandExecutor->execute($command);
    }

    public function suspend(): void
    {
        $this->validateParameters();
        $command = new SuspendJobDefinitionCmd($this);
        $this->commandExecutor->execute($command);
    }

    protected function validateParameters(): void
    {
        EnsureUtil::ensureOnlyOneNotNull(
            "Need to specify either a job definition id, a process definition id or a process definition key.",
            $this->jobDefinitionId,
            $this->processDefinitionId,
            $this->processDefinitionKey
        );

        if ($this->isProcessDefinitionTenantIdSet && ($this->jobDefinitionId !== null || $this->processDefinitionId !== null)) {
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

    public function getProcessDefinitionTenantId(): string
    {
        return $this->processDefinitionTenantId;
    }

    public function isProcessDefinitionTenantIdSet(): bool
    {
        return $this->isProcessDefinitionTenantIdSet;
    }

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    public function isIncludeJobs(): bool
    {
        return $this->includeJobs;
    }

    public function getExecutionDate(): string
    {
        return $this->executionDate;
    }
}
