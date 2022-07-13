<?php

namespace Jabe\Engine\Impl\Management;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\{
    ActivateJobCmd,
    CommandLogger,
    SuspendJobCmd
};
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Management\{
    UpdateJobSuspensionStateBuilderInterface,
    UpdateJobSuspensionStateSelectBuilderInterface,
    UpdateJobSuspensionStateTenantBuilderInterface
};

class UpdateJobSuspensionStateBuilderImpl implements UpdateJobSuspensionStateBuilderInterface, UpdateJobSuspensionStateSelectBuilderInterface, UpdateJobSuspensionStateTenantBuilderInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $commandExecutor;

    protected $jobId;
    protected $jobDefinitionId;

    protected $processInstanceId;

    protected $processDefinitionKey;
    protected $processDefinitionId;

    protected $processDefinitionTenantId;
    protected $isProcessDefinitionTenantIdSet = false;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function byJobId(string $jobId): UpdateJobSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("jobId", "jobId)", $jobId);
        $this->jobId = $jobId;
        return $this;
    }

    public function byJobDefinitionId(string $jobDefinitionId): UpdateJobSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("jobDefinitionId", "jobDefinitionId", $jobDefinitionId);
        $this->jobDefinitionId = $jobDefinitionId;
        return $this;
    }

    public function byProcessInstanceId(string $processInstanceId): UpdateJobSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("processInstanceId", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function byProcessDefinitionId(string $processDefinitionId): UpdateJobSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function byProcessDefinitionKey(string $processDefinitionKey): UpdateJobSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("processDefinitionKey", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionWithoutTenantId(): UpdateJobSuspensionStateBuilderImpl
    {
        $this->processDefinitionTenantId = null;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function processDefinitionTenantId(string $tenantId): UpdateJobSuspensionStateBuilderImpl
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $tenantId);

        $this->processDefinitionTenantId = $tenantId;
        $this->isProcessDefinitionTenantIdSet = true;
        return $this;
    }

    public function activate(): void
    {
        $this->validateParameters();
        $command = new ActivateJobCmd($this);
        $this->commandExecutor->execute($command);
    }

    public function suspend(): void
    {
        $this->validateParameters();
        $command = new SuspendJobCmd($this);
        $this->commandExecutor->execute($command);
    }

    protected function validateParameters(): void
    {
        EnsureUtil::ensureOnlyOneNotNull(
            "Need to specify either a job id, a job definition id, a process instance id, a process definition id or a process definition key.",
            $this->jobId,
            $this->jobDefinitionId,
            $this->processInstanceId,
            $this->processDefinitionId,
            $this->processDefinitionKey
        );

        if ($this->isProcessDefinitionTenantIdSet && ($this->jobId !== null || $this->jobDefinitionId !== null || $this->processInstanceId !== null || $this->processDefinitionId !== null)) {
            //throw LOG.exceptionUpdateSuspensionStateForTenantOnlyByProcessDefinitionKey();
            throw new \Exception("exceptionUpdateSuspensionStateForTenantOnlyByProcessDefinitionKey");
        }

        EnsureUtil::ensureNotNull("commandExecutor", "commandExecutor", $commandExecutor);
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

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }
}
