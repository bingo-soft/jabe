<?php

namespace Jabe\Engine\Impl\Batch\Builder;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Authorization\PermissionInterface;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    BatchEntity,
    BatchJobHandlerInterface
};
use Jabe\Engine\Impl\Interceptor\CommandContext;

class BatchBuilder
{
    protected $commandContext;

    protected $config;
    protected $tenantId;
    protected $type;

    protected $totalJobsCount;

    protected $permission;
    protected $permissionHandler;

    protected $operationLogInstanceCountHandler;
    protected $operationLogHandler;

    public function __construct(CommandContext $commandContext)
    {
        $this->commandContext = $commandContext;
    }

    public function tenantId(string $tenantId): BatchBuilder
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    public function config(BatchConfiguration $config): BatchBuilder
    {
        $this->config = $config;
        return $this;
    }

    public function type(string $batchType): BatchBuilder
    {
        $this->type = $batchType;
        return $this;
    }

    public function totalJobs(int $totalJobsCount): BatchBuilder
    {
        $this->totalJobsCount = $totalJobsCount;
        return $this;
    }

    public function permission(PermissionInterface $permission): BatchBuilder
    {
        $this->permission = $permission;
        return $this;
    }

    public function permissionHandler(PermissionHandlerInterface $permissionCheckHandler): BatchBuilder
    {
        $this->permissionHandler = $permissionCheckHandler;
        return $this;
    }

    public function operationLogHandler($operationLogHandler): BatchBuilder
    {
        if ($operationLogHandler instanceof OperationLogInstanceCountHandlerInterface) {
            $this->operationLogInstanceCountHandler = $operationLogHandler;
        } elseif ($operationLogHandler instanceof OperationLogHandlerInterface) {
            $this->operationLogHandler = $operationLogHandler;
        }
        return $this;
    }

    public function build(): BatchInterface
    {
        $this->checkPermissions();

        $batch = new BatchEntity();
        $this->configure($batch);
        $this->save($batch);

        $this->writeOperationLog();

        return $batch;
    }

    protected function checkPermissions(): void
    {
        if ($this->permission === null && $this->permissionHandler === null) {
            throw new ProcessEngineException("No permission check performed!");
        }

        if ($this->permission !== null) {
            $checkers = $this->commandContext->getProcessEngineConfiguration()
                ->getCommandCheckers();
            foreach ($checkers as $checker) {
                $checker->checkCreateBatch($this->permission);
            }
        }

        if ($this->permissionHandler !== null) {
            $this->permissionHandler->check($this->commandContext);
        }
    }

    protected function configure(BatchEntity $batch): BatchEntity
    {
        $engineConfig = $this->commandContext->getProcessEngineConfiguration();

        $jobHandlers = $engineConfig->getJobHandlers();
        $jobHandler = $jobHandlers[$this->type];

        $type = $jobHandler->getType();
        $batch->setType($type);

        $invocationPerBatchJobCount = $this->calculateInvocationsPerBatchJob($type);
        $batch->setInvocationsPerBatchJob($invocationPerBatchJobCount);

        $batch->setTenantId($this->tenantId);

        $configAsBytes = $jobHandler->writeConfiguration($this->config);
        $batch->setConfigurationBytes($configAsBytes);

        $this->setTotalJobs($batch, $invocationPerBatchJobCount);

        $jobCount = $engineConfig->getBatchJobsPerSeed();
        $batch->setBatchJobsPerSeed($jobCount);

        return $batch;
    }

    protected function setTotalJobs(BatchEntity $batch, int $invocationPerBatchJobCount): void
    {
        if ($this->totalJobsCount !== null) {
            $batch->setTotalJobs($this->totalJobsCount);
        } else {
            $instanceIds = $this->config->getIds();

            $instanceCount = count($instanceIds);
            $totalJobsCount = $this->calculateTotalJobs($instanceCount, $invocationPerBatchJobCount);

            $batch->setTotalJobs($totalJobsCount);
        }
    }

    protected function save(BatchEntity $batch): void
    {
        $this->commandContext->getBatchManager()->insertBatch($batch);

        $seedDeploymentId = null;
        if ($this->config->getIdMappings() !== null && !empty($this->config->getIdMappings())) {
            $seedDeploymentId = $this->config->getIdMappings()[0]->getDeploymentId();
        }

        $batch->createSeedJobDefinition($seedDeploymentId);
        $batch->createMonitorJobDefinition();
        $batch->createBatchJobDefinition();

        $batch->fireHistoricStartEvent();

        $batch->createSeedJob();
    }

    public function writeOperationLog(): void
    {
        if ($this->operationLogInstanceCountHandler === null && $this->operationLogHandler === null) {
            throw new ProcessEngineException("No operation log handler specified!");
        }
        if ($this->operationLogInstanceCountHandler !== null) {
            $instanceIds = $this->config->getIds();

            $instanceCount = count($instanceIds);
            $this->operationLogInstanceCountHandler->write($this->commandContext, $instanceCount);
        } else {
            $this->operationLogHandler->write($commandContext);
        }
    }

    protected function calculateTotalJobs(int $instanceCount, int $invocationPerBatchJobCount): int
    {
        if ($instanceCount == 0 || $invocationPerBatchJobCount == 0) {
            return 0;
        }

        if ($instanceCount % $invocationPerBatchJobCount == 0) {
            return $instanceCount / $invocationPerBatchJobCount;
        }

        return ($instanceCount / $invocationPerBatchJobCount) + 1;
    }

    protected function calculateInvocationsPerBatchJob(string $batchType): int
    {
        $engineConfig = $this->commandContext->getProcessEngineConfiguration();

        $invocationsPerBatchJobByBatchType = $engineConfig->getInvocationsPerBatchJobByBatchType();

        if (array_key_exists($batchType, $invocationsPerBatchJobByBatchType)) {
            return $invocationsPerBatchJobByBatchType[$batchType];
        } else {
            return $engineConfig->getInvocationsPerBatchJob();
        }
    }
}
