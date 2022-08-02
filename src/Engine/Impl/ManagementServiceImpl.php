<?php

namespace Jabe\Engine\Impl;

use Doctrine\DBAL\Connection;
use Jabe\Engine\Application\{
    ProcessApplicationReferenceInterface,
    ProcessApplicationRegistrationInterface
};
use Jabe\Engine\{
    ManagementServiceInterface,
    ProcessEngineConfiguration
};
use Jabe\Engine\Batch\{
    BatchInterface,
    BatchQueryInterface,
    BatchStatisticsQueryInterface
};
use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;
use Jabe\Engine\Impl\Batch\{
    BatchQueryImpl,
    BatchStatisticsQueryImpl,
    DeleteBatchCmd
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Cmd\{
    ActivateBatchCmd,
    DeleteJobCmd,
    DeleteLicenseKeyCmd,
    DeleteMetricsCmd,
    DeletePropertyCmd,
    DeleteTaskMetricsCmd,
    GetHistoryLevelCmd,
    GetJobExceptionStacktraceCmd,
    GetLicenseKeyCmd,
    GetProcessApplicationForDeploymentCmd,
    GetPropertiesCmd,
    GetTableCountCmd,
    GetTableMetaDataCmd,
    GetTableNameCmd,
    GetTelemetryDataCmd,
    GetUniqueTaskWorkerCountCmd,
    IsTelemetryEnabledCmd,
    PurgeDatabaseAndCacheCmd,
    RecalculateJobDuedateCmd,
    RegisterDeploymentCmd,
    RegisterProcessApplicationCmd,
    ReportDbMetricsCmd,
    SetJobDefinitionPriorityCmd,
    SetJobDuedateCmd,
    SetJobPriorityCmd,
    SetJobRetriesCmd,
    SetJobsRetriesBatchCmd,
    SetJobsRetriesCmd,
    SetLicenseKeyCmd,
    SetPropertyCmd,
    SuspendBatchCmd,
    TelemetryConfigureCmd,
    UnregisterDeploymentCmd,
    UnregisterProcessApplicationCmd
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\Sql\{
    DbSqlSession,
    DbSqlSessionFactory
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\JobExecutor\ExecuteJobHelper;
use Jabe\Engine\Impl\Management\{
    PurgeReport,
    UpdateJobDefinitionSuspensionStateBuilderImpl,
    UpdateJobSuspensionStateBuilderImpl
};
use Jabe\Engine\Impl\Metrics\{
    MetricsQueryImpl,
    MetricsRegistry
};
use Jabe\Engine\Impl\Telemetry\Dto\LicenseKeyDataImpl;
use Jabe\Engine\Management\{
    ActivityStatisticsQueryInterface,
    DeploymentStatisticsQueryInterface,
    JobDefinitionQueryInterface,
    MetricsQueryInterface,
    ProcessDefinitionStatisticsQueryInterface,
    SchemaLogQueryInterface,
    TableMetaData,
    TablePageQueryInterface,
    UpdateJobDefinitionSuspensionStateSelectBuilderInterface,
    UpdateJobSuspensionStateSelectBuilderInterface
};
use Jabe\Engine\Runtime\{
    JobQueryInterface,
    ProcessInstanceQueryInterface
};
use Jabe\Engine\Telemetry\TelemetryDataInterface;

class ManagementServiceImpl extends ServiceImpl implements ManagementServiceInterface
{
    protected $processEngineConfiguration;

    public function __construct(ProcessEngineConfiguration $processEngineConfiguration)
    {
        $this->processEngineConfiguration = $processEngineConfiguration;
    }

    public function registerProcessApplication(string $deploymentId, ProcessApplicationReferenceInterface $reference): ProcessApplicationRegistrationInterface
    {
        return $this->commandExecutor->execute(new RegisterProcessApplicationCmd($deploymentId, $reference));
    }

    public function unregisterProcessApplication($deploymentIds, bool $removeProcessesFromCache): void
    {
        $this->commandExecutor->execute(new UnregisterProcessApplicationCmd($deploymentIds, $removeProcessesFromCache));
    }

    public function getProcessApplicationForDeployment(string $deploymentId): string
    {
        return $this->commandExecutor->execute(new GetProcessApplicationForDeploymentCmd($deploymentId));
    }

    public function getTableCount(): array
    {
        return $this->commandExecutor->execute(new GetTableCountCmd());
    }

    public function getTableName(string $activitiEntityClass): string
    {
        return $this->commandExecutor->execute(new GetTableNameCmd($activitiEntityClass));
    }

    public function getTableMetaData(string $tableName): TableMetaData
    {
        return $this->commandExecutor->execute(new GetTableMetaDataCmd($tableName));
    }

    public function executeJob(string $jobId): void
    {
        ExecuteJobHelper::executeJob($jobId, $this->commandExecutor);
    }

    public function deleteJob(string $jobId): void
    {
        $this->commandExecutor->execute(new DeleteJobCmd($jobId));
    }

    public function setJobRetries($jobIds, int $retries): void
    {
        $this->commandExecutor->execute(new SetJobsRetriesCmd($jobIds, $retries));
    }

    public function setJobRetriesAsync($ids, $queryOrRetries, $historicQueryOrRetries = null, $retries = null): BatchInterface
    {
        if ($historicQueryOrRetries instanceof HistoricProcessInstanceQueryInterface) {
            return $this->commandExecutor->execute(
                new SetJobsRetriesByProcessBatchCmd($ids, $queryOrRetries, $historicQueryOrRetries, $retries)
            );
        } elseif ($queryOrRetries instanceof ProcessInstanceQueryInterface && $retries === null) {
            return $this->commandExecutor->execute(
                new SetJobsRetriesByProcessBatchCmd($ids, $queryOrRetries, null, $historicQueryOrRetries)
            );
        } elseif ($ids instanceof JobQueryInterface) {
            return $this->commandExecutor->execute(new SetJobsRetriesBatchCmd(null, $ids, $queryOrRetries));
        } elseif (is_array($ids)) {
            return $this->commandExecutor->execute(new SetJobsRetriesBatchCmd($ids, null, $queryOrRetries));
        }
    }

    public function setJobRetriesByJobDefinitionId(string $jobDefinitionId, int $retries): void
    {
        $this->commandExecutor->execute(new SetJobRetriesCmd(null, $jobDefinitionId, $retries));
    }

    public function setJobDuedate(string $jobId, string $newDuedate, bool $cascade = false): void
    {
        $this->commandExecutor->execute(new SetJobDuedateCmd($jobId, $newDuedate, $cascade));
    }

    public function recalculateJobDuedate(string $jobId, bool $creationDateBased): void
    {
        $this->commandExecutor->execute(new RecalculateJobDuedateCmd($jobId, $creationDateBased));
    }

    public function setJobPriority(string $jobId, int $priority): void
    {
        $this->commandExecutor->execute(new SetJobPriorityCmd($jobId, $priority));
    }

    public function createTablePageQuery(): TablePageQueryInterface
    {
        return new TablePageQueryImpl($this->commandExecutor);
    }

    public function createJobQuery(): JobQueryInterface
    {
        return new JobQueryImpl($this->commandExecutor);
    }

    public function createJobDefinitionQuery(): JobDefinitionQueryInterface
    {
        return new JobDefinitionQueryImpl($this->commandExecutor);
    }

    public function getJobExceptionStacktrace(string $jobId): string
    {
        return $this->commandExecutor->execute(new GetJobExceptionStacktraceCmd($jobId));
    }

    public function getProperties(): array
    {
        return $this->commandExecutor->execute(new GetPropertiesCmd());
    }

    public function setProperty(string $name, string $value): void
    {
        $this->commandExecutor->execute(new SetPropertyCmd($name, $value));
    }

    public function deleteProperty(string $name): void
    {
        $this->commandExecutor->execute(new DeletePropertyCmd($name));
    }

    public function setLicenseKey(string $licenseKey): void
    {
        $this->commandExecutor->execute(new SetLicenseKeyCmd($licenseKey));
    }

    public function getLicenseKey(): string
    {
        return $this->commandExecutor->execute(new GetLicenseKeyCmd());
    }

    public function deleteLicenseKey(): void
    {
        $this->commandExecutor->execute(new DeleteLicenseKeyCmd(true));
    }

    public function databaseSchemaUpgrade(Connection $connection, string $catalog, string $schema): string
    {
        return $this->commandExecutor->execute(new DbSchemaUpgradeCmd($connection, $catalog, $schema));
    }

    /**
     * Purges the database and the deployment cache.
     */
    public function purge(): PurgeReport
    {
        return $this->commandExecutor->execute(new PurgeDatabaseAndCacheCmd());
    }

    public function createProcessDefinitionStatisticsQuery(): ProcessDefinitionStatisticsQueryInterface
    {
        return new ProcessDefinitionStatisticsQueryImpl($this->commandExecutor);
    }

    public function createActivityStatisticsQuery(string $processDefinitionId): ActivityStatisticsQueryInterface
    {
        return new ActivityStatisticsQueryImpl($processDefinitionId, $this->commandExecutor);
    }

    public function createDeploymentStatisticsQuery(): DeploymentStatisticsQueryInterface
    {
        return new DeploymentStatisticsQueryImpl($this->commandExecutor);
    }

    public function getRegisteredDeployments(): array
    {
        return $this->commandExecutor->execute(new GetRegisteredDeploymentsCmd());
    }

    public function registerDeploymentForJobExecutor(string $deploymentId): void
    {
        $this->commandExecutor->execute(new RegisterDeploymentCmd($deploymentId));
    }

    public function unregisterDeploymentForJobExecutor(string $deploymentId): void
    {
        $this->commandExecutor->execute(new UnregisterDeploymentCmd($deploymentId));
    }

    public function activateJobDefinitionById(string $jobDefinitionId, bool $activateJobs = false, string $activationDate = null): void
    {
        if (!$activateJobs && $activationDate === null) {
            $this->updateJobDefinitionSuspensionState()
            ->byJobDefinitionId($jobDefinitionId)
            ->activate();
        } elseif ($activationDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byJobDefinitionId($jobDefinitionId)
                ->includeJobs($activateJobs)
                ->activate();
        } else {
            $this->updateJobDefinitionSuspensionState()
                ->byJobDefinitionId($jobDefinitionId)
                ->includeJobs($activateJobs)
                ->executionDate($activationDate)
                ->activate();
        }
    }

    public function suspendJobDefinitionById(string $jobDefinitionId, bool $suspendJobs = false, string $suspensionDate = null): void
    {
        if (!$suspendJobs && $suspensionDate === null) {
            $this->updateJobDefinitionSuspensionState()
            ->byJobDefinitionId($jobDefinitionId)
            ->suspend();
        } elseif ($suspensionDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byJobDefinitionId($jobDefinitionId)
                ->includeJobs($suspendJobs)
                ->suspend();
        } else {
            $this->updateJobDefinitionSuspensionState()
            ->byJobDefinitionId($jobDefinitionId)
            ->includeJobs($suspendJobs)
            ->executionDate($suspensionDate)
            ->suspend();
        }
    }

    public function activateJobDefinitionByProcessDefinitionId(string $processDefinitionId, bool $activateJobs = false, string $activationDate = null): void
    {
        if (!$activateJobs && $activationDate === null) {
            $this->updateJobDefinitionSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->activate();
        } elseif ($activationDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionId($processDefinitionId)
                ->includeJobs($activateJobs)
                ->activate();
        } else {
            $this->updateJobDefinitionSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->includeJobs($activateJobs)
            ->executionDate($activationDate)
            ->activate();
        }
    }

    public function suspendJobDefinitionByProcessDefinitionId(string $processDefinitionId, bool $suspendJobs = false, string $suspensionDate = null): void
    {
        if (!$suspendJobs && $suspensionDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionId($processDefinitionId)
                ->suspend();
        } elseif ($suspensionDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionId($processDefinitionId)
                ->includeJobs($suspendJobs)
                ->suspend();
        } else {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionId($processDefinitionId)
                ->includeJobs($suspendJobs)
                ->executionDate($suspensionDate)
                ->suspend();
        }
    }

    public function activateJobDefinitionByProcessDefinitionKey(string $processDefinitionKey, bool $activateJobs = false, string $activationDate = null): void
    {
        if (!$activateJobs && $activationDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionKey($processDefinitionKey)
                ->activate();
        } elseif ($activationDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionKey($processDefinitionKey)
                ->includeJobs($activateJobs)
                ->activate();
        } else {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionKey($processDefinitionKey)
                ->includeJobs($activateJobs)
                ->executionDate($activationDate)
                ->activate();
        }
    }

    public function suspendJobDefinitionByProcessDefinitionKey(string $processDefinitionKey, bool $suspendJobs = false, string $suspensionDate = null): void
    {
        if (!$suspendJobs && $suspensionDate === null) {
            $this->updateJobDefinitionSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->suspend();
        } elseif ($suspensionDate === null) {
            $this->updateJobDefinitionSuspensionState()
                ->byProcessDefinitionKey($processDefinitionKey)
                ->includeJobs($suspendJobs)
                ->suspend();
        } else {
            $this->updateJobDefinitionSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->includeJobs($suspendJobs)
            ->executionDate($suspensionDate)
            ->suspend();
        }
    }

    public function updateJobDefinitionSuspensionState(): UpdateJobDefinitionSuspensionStateSelectBuilderInterface
    {
        return new UpdateJobDefinitionSuspensionStateBuilderImpl($this->commandExecutor);
    }

    public function activateJobById(string $jobId): void
    {
        $this->updateJobSuspensionState()
            ->byJobId(jobId)
            ->activate();
    }

    public function activateJobByProcessInstanceId(string $processInstanceId): void
    {
        $this->updateJobSuspensionState()
            ->byProcessInstanceId($processInstanceId)
            ->activate();
    }

    public function activateJobByJobDefinitionId(string $jobDefinitionId): void
    {
        $this->updateJobSuspensionState()
            ->byJobDefinitionId($jobDefinitionId)
            ->activate();
    }

    public function activateJobByProcessDefinitionId(string $processDefinitionId): void
    {
        $this->updateJobSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->activate();
    }

    public function activateJobByProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->updateJobSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->activate();
    }

    public function suspendJobById(string $jobId): void
    {
        $this->updateJobSuspensionState()
            ->byJobId($jobId)
            ->suspend();
    }

    public function suspendJobByJobDefinitionId(string $jobDefinitionId): void
    {
        $this->updateJobSuspensionState()
            ->byJobDefinitionId($jobDefinitionId)
            ->suspend();
    }

    public function suspendJobByProcessInstanceId(string $processInstanceId): void
    {
        $this->updateJobSuspensionState()
            ->byProcessInstanceId($processInstanceId)
            ->suspend();
    }

    public function suspendJobByProcessDefinitionId(string $processDefinitionId): void
    {
        $this->updateJobSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->suspend();
    }

    public function suspendJobByProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->updateJobSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->suspend();
    }

    public function updateJobSuspensionState(): UpdateJobSuspensionStateSelectBuilderInterface
    {
        return new UpdateJobSuspensionStateBuilderImpl($this->commandExecutor);
    }

    public function getHistoryLevel(): int
    {
        return $this->commandExecutor->execute(new GetHistoryLevelCmd());
    }

    public function createMetricsQuery(): MetricsQueryInterface
    {
        return new MetricsQueryImpl($this->commandExecutor);
    }

    public function deleteMetrics(string $timestamp = null, string $reporter = null): void
    {
        $this->commandExecutor->execute(new DeleteMetricsCmd($timestamp, $reporter));
    }

    public function reportDbMetricsNow(): void
    {
        $this->commandExecutor->execute(new ReportDbMetricsCmd());
    }

    public function getUniqueTaskWorkerCount(string $startTime, string $endTime): int
    {
        return $this->commandExecutor->execute(new GetUniqueTaskWorkerCountCmd($startTime, $endTime));
    }

    public function deleteTaskMetrics(string $timestamp): void
    {
        $this->commandExecutor->execute(new DeleteTaskMetricsCmd($timestamp));
    }

    public function setOverridingJobPriorityForJobDefinition(string $jobDefinitionId, int $priority, bool $cascade = false): void
    {
        $this->commandExecutor->execute(new SetJobDefinitionPriorityCmd($jobDefinitionId, $priority, $cascade));
    }

    public function clearOverridingJobPriorityForJobDefinition(string $jobDefinitionId): void
    {
        $this->commandExecutor->execute(new SetJobDefinitionPriorityCmd($jobDefinitionId, null, false));
    }

    public function createBatchQuery(): BatchQueryInterface
    {
        return new BatchQueryImpl($this->commandExecutor);
    }

    public function deleteBatch(string $batchId, bool $cascade): void
    {
        $this->commandExecutor->execute(new DeleteBatchCmd($batchId, $cascade));
    }

    public function suspendBatchById(string $batchId): void
    {
        $this->commandExecutor->execute(new SuspendBatchCmd($batchId));
    }

    public function activateBatchById(string $batchId): void
    {
        $this->commandExecutor->execute(new ActivateBatchCmd($batchId));
    }

    public function createBatchStatisticsQuery(): BatchStatisticsQueryInterface
    {
        return new BatchStatisticsQueryImpl($this->commandExecutor);
    }

    public function createSchemaLogQuery(): SchemaLogQueryInterface
    {
        return new SchemaLogQueryImpl($this->commandExecutor);
    }

    public function toggleTelemetry(bool $enabled): void
    {
        $this->commandExecutor->execute(new TelemetryConfigureCmd($enabled));
    }

    public function isTelemetryEnabled(): bool
    {
        return $this->commandExecutor->execute(new IsTelemetryEnabledCmd());
    }

    public function getTelemetryData(): TelemetryDataInterface
    {
        return $this->commandExecutor->execute(new GetTelemetryDataCmd());
    }

    /**
     * Adds the web application name to the telemetry data of the engine.
     *
     * @param webapp
     *          the web application that is used with the engine
     * @return whether the web application was successfully added or not
     */
    public function addWebappToTelemetry(string $webapp): bool
    {
        $telemetryRegistry = $this->processEngineConfiguration->getTelemetryRegistry();
        if ($telemetryRegistry !== null) {
            $telemetryRegistry->addWebapp($webapp);
            return true;
        }
        return false;
    }

    /**
     * Adds the application server information to the telemetry data of the engine.
     *
     * @param appServerInfo
     *          a String containing information about the application server
     */
    public function addApplicationServerInfoToTelemetry(string $appServerInfo): void
    {
        $telemetryRegistry = $this->processEngineConfiguration->getTelemetryRegistry();
        if ($telemetryRegistry !== null) {
            $telemetryRegistry->setApplicationServer($appServerInfo);
        }
    }

    /**
     * Sets license key information to the telemetry data of the engine.
     *
     * @param licenseKeyData
     *          a data object containing various pieces of information
     *          about the installed license
     */
    public function setLicenseKeyForTelemetry(LicenseKeyDataImpl $licenseKeyData): void
    {
        $telemetryRegistry = $this->processEngineConfiguration->getTelemetryRegistry();
        if ($telemetryRegistry !== null) {
            $telemetryRegistry->setLicenseKey($licenseKeyData);
        }
    }

    public function getLicenseKeyFromTelemetry(): LicenseKeyDataImpl
    {
        $telemetryRegistry = $this->processEngineConfiguration->getTelemetryRegistry();
        if ($telemetryRegistry !== null) {
            return $telemetryRegistry->getLicenseKey();
        }
        return null;
    }

    public function clearTelemetryData(): void
    {
        $telemetryRegistry = $this->processEngineConfiguration->getTelemetryRegistry();
        if ($telemetryRegistry !== null) {
            $telemetryRegistry->clear();
        }
        $metricsRegistry = $this->processEngineConfiguration->getMetricsRegistry();
        if ($metricsRegistry !== null) {
            $metricsRegistry->clearTelemetryMetrics();
        }
        $this->deleteMetrics(null);
    }
}
