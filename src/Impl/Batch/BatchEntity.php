<?php

namespace Jabe\Impl\Batch;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\{
    HistoricIncidentManager,
    HistoricJobLogManager,
    JobDefinitionEntity,
    JobDefinitionManager,
    JobEntity,
    NameableInterface,
    SuspensionState,
    VariableInstanceEntity,
    VariableInstanceManager
};
use Jabe\Impl\Persistence\Entity\Util\ByteArrayField;
use Jabe\Impl\Util\ClockUtil;
use Jabe\Repository\ResourceTypes;

class BatchEntity implements BatchInterface, DbEntityInterface, HasDbReferencesInterface, NameableInterface, HasDbRevisionInterface
{
    public static $BATCH_SEED_JOB_DECLARATION;
    public static $BATCH_MONITOR_JOB_DECLARATION;

    // persistent
    protected $id;
    protected $type;

    protected int $totalJobs = 0;
    protected int $jobsCreated = 0;
    protected int $batchJobsPerSeed = 0;
    protected int $invocationsPerBatchJob = 0;

    protected $seedJobDefinitionId;
    protected $monitorJobDefinitionId;
    protected $batchJobDefinitionId;

    protected $configuration;

    protected $tenantId;
    protected $createUserId;

    protected $suspensionState;

    protected int $revision = 0;

    // transient
    protected $seedJobDefinition;
    protected $monitorJobDefinition;
    protected $batchJobDefinition;

    protected $batchJobHandler;

    protected $startTime;
    protected $executionStartTime;

    public function __construct()
    {
        if (self::$BATCH_SEED_JOB_DECLARATION == null) {
            self::$BATCH_SEED_JOB_DECLARATION = new BatchSeedJobDeclaration();
        }
        if (self::$BATCH_MONITOR_JOB_DECLARATION == null) {
            self::$BATCH_MONITOR_JOB_DECLARATION = new BatchMonitorJobDeclaration();
        }
        $this->configuration = new ByteArrayField($this, ResourceTypes::runtime());
        $this->suspensionState = SuspensionState::active()->getStateCode();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getTotalJobs(): int
    {
        return $this->totalJobs;
    }

    public function setTotalJobs(int $totalJobs): void
    {
        $this->totalJobs = $totalJobs;
    }

    public function getJobsCreated(): int
    {
        return $this->jobsCreated;
    }

    public function setJobsCreated(int $jobsCreated): void
    {
        $this->jobsCreated = $jobsCreated;
    }

    public function getBatchJobsPerSeed(): int
    {
        return $this->batchJobsPerSeed;
    }

    public function setBatchJobsPerSeed(int $batchJobsPerSeed): void
    {
        $this->batchJobsPerSeed = $batchJobsPerSeed;
    }

    public function getInvocationsPerBatchJob(): int
    {
        return $this->invocationsPerBatchJob;
    }

    public function setInvocationsPerBatchJob(int $invocationsPerBatchJob): void
    {
        $this->invocationsPerBatchJob = $invocationsPerBatchJob;
    }

    public function getSeedJobDefinitionId(): ?string
    {
        return $this->seedJobDefinitionId;
    }

    public function setSeedJobDefinitionId(?string $seedJobDefinitionId): void
    {
        $this->seedJobDefinitionId = $seedJobDefinitionId;
    }

    public function getMonitorJobDefinitionId(): ?string
    {
        return $this->monitorJobDefinitionId;
    }

    public function setMonitorJobDefinitionId(?string $monitorJobDefinitionId): void
    {
        $this->monitorJobDefinitionId = $monitorJobDefinitionId;
    }

    public function getBatchJobDefinitionId(): ?string
    {
        return $this->batchJobDefinitionId;
    }

    public function setBatchJobDefinitionId(?string $batchJobDefinitionId): void
    {
        $this->batchJobDefinitionId = $batchJobDefinitionId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getCreateUserId(): ?string
    {
        return $this->createUserId;
    }

    public function setCreateUserId(?string $createUserId): void
    {
        $this->createUserId = $createUserId;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration->getByteArrayId();
    }

    public function setConfiguration(?string $configuration): void
    {
        $this->configuration->setByteArrayId($configuration);
    }

    public function setSuspensionState(int $state): void
    {
        $this->suspensionState = $state;
    }

    public function getSuspensionState(): int
    {
        return $this->suspensionState;
    }

    public function isSuspended(): bool
    {
        return $this->suspensionState == SuspensionState::suspended()->getStateCode();
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    // transient

    public function getSeedJobDefinition(): JobDefinitionEntity
    {
        if ($this->seedJobDefinition == null && $this->seedJobDefinitionId !== null) {
            $this->seedJobDefinition = Context::getCommandContext()->getJobDefinitionManager()->findById($this->seedJobDefinitionId);
        }
        return $this->seedJobDefinition;
    }

    public function getMonitorJobDefinition(): JobDefinitionEntity
    {
        if ($this->monitorJobDefinition == null && $this->monitorJobDefinitionId !== null) {
            $this->monitorJobDefinition = Context::getCommandContext()->getJobDefinitionManager()->findById($this->monitorJobDefinitionId);
        }
        return $this->monitorJobDefinition;
    }

    public function getBatchJobDefinition(): JobDefinitionEntity
    {
        if ($this->batchJobDefinition == null && $this->batchJobDefinitionId !== null) {
            $this->batchJobDefinition = Context::getCommandContext()->getJobDefinitionManager()->findById($this->batchJobDefinitionId);
        }

        return $this->batchJobDefinition;
    }

    public function getConfigurationBytes(): ?string
    {
        return $this->configuration->getByteArrayValue();
    }

    public function setConfigurationBytes(?string $configuration): void
    {
        $this->configuration->setByteArrayValue($configuration);
    }

    public function getBatchJobHandler(): BatchJobHandlerInterface
    {
        if ($this->batchJobHandler == null) {
            $this->batchJobHandler = Context::getCommandContext()->getProcessEngineConfiguration()->getBatchHandlers()->get($this->type);
        }
        return $this->batchJobHandler;
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["jobsCreated"] = $this->jobsCreated;
        return $persistentState;
    }

    public function createSeedJobDefinition(?string $deploymentId): JobDefinitionEntity
    {
        $this->seedJobDefinition = new JobDefinitionEntity(self::$BATCH_SEED_JOB_DECLARATION);
        $this->seedJobDefinition->setJobConfiguration($this->id);
        $this->seedJobDefinition->setTenantId($this->tenantId);
        $this->seedJobDefinition->setDeploymentId($this->deploymentId);

        Context::getCommandContext()->getJobDefinitionManager()->insert($this->seedJobDefinition);

        $this->seedJobDefinitionId = $this->seedJobDefinition->getId();

        return $this->seedJobDefinition;
    }

    public function createMonitorJobDefinition(): JobDefinitionEntity
    {
        $this->monitorJobDefinition = new JobDefinitionEntity(self::$BATCH_MONITOR_JOB_DECLARATION);
        $this->monitorJobDefinition->setJobConfiguration($this->id);
        $this->monitorJobDefinition->setTenantId($this->tenantId);

        Context::getCommandContext()->getJobDefinitionManager()->insert($this->monitorJobDefinition);

        $this->monitorJobDefinitionId = $this->monitorJobDefinition->getId();

        return $this->monitorJobDefinition;
    }

    public function createBatchJobDefinition(): JobDefinitionEntity
    {
        $this->batchJobDefinition = new JobDefinitionEntity($this->getBatchJobHandler()->getJobDeclaration());
        $this->batchJobDefinition->setJobConfiguration($this->id);
        $this->batchJobDefinition->setTenantId($this->tenantId);

        Context::getCommandContext()->getJobDefinitionManager()->insert($this->batchJobDefinition);

        $this->batchJobDefinitionId = $this->batchJobDefinition->getId();

        return $this->batchJobDefinition;
    }

    public function createSeedJob(): JobEntity
    {
        $seedJob = self::$BATCH_SEED_JOB_DECLARATION->createJobInstance($this);

        Context::getCommandContext()->getJobManager()->insertAndHintJobExecutor($seedJob);

        return $seedJob;
    }

    public function deleteSeedJob(): void
    {
        $seedJobs = Context::getCommandContext()
            ->getJobManager()
            ->findJobsByJobDefinitionId($this->seedJobDefinitionId);

        foreach ($seedJobs as $job) {
            $job->delete();
        }
    }

    public function createMonitorJob(bool $setDueDate): JobEntity
    {
        // Maybe use an other job declaration
        $monitorJob = self::$BATCH_MONITOR_JOB_DECLARATION->createJobInstance($this);
        if ($setDueDate) {
            $monitorJob->setDuedate($this->calculateMonitorJobDueDate());
        }

        Context::getCommandContext()
            ->getJobManager()->insertAndHintJobExecutor($monitorJob);

        return $monitorJob;
    }

    protected function calculateMonitorJobDueDate(): ?string
    {
        $conf = Context::getCommandContext()->getProcessEngineConfiguration();
        $pollTime = $conf->getBatchPollTime();
        $dueTime = ClockUtil::getCurrentTime()->getTimestamp() + $pollTime;
        return (new \DateTime())->setTimestamp($dueTime)->format('c');
    }

    public function deleteMonitorJob(): void
    {
        $monitorJobs = Context::getCommandContext()
            ->getJobManager()
            ->findJobsByJobDefinitionId($this->monitorJobDefinitionId);

        foreach ($monitorJobs as $monitorJob) {
            $monitorJob->delete();
        }
    }

    public function delete(bool $cascadeToHistory, bool $deleteJobs): void
    {
        $commandContext = Context::getCommandContext();

        if (
            BatchInterface::TYPE_SET_VARIABLES == $this->type ||
            BatchInterface::TYPE_PROCESS_INSTANCE_MIGRATION == $this->type ||
            BatchInterface::TYPE_CORRELATE_MESSAGE == $this->type
        ) {
            $this->deleteVariables($commandContext);
        }

        $this->deleteSeedJob();
        $this->deleteMonitorJob();
        if ($deleteJobs) {
            $this->getBatchJobHandler()->deleteJobs($this);
        }

        $jobDefinitionManager = $commandContext->getJobDefinitionManager();
        $jobDefinitionManager->delete($this->getSeedJobDefinition());
        $jobDefinitionManager->delete($this->getMonitorJobDefinition());
        $jobDefinitionManager->delete($this->getBatchJobDefinition());

        $commandContext->getBatchManager()->delete($this);
        $this->configuration->deleteByteArrayValue();

        $this->fireHistoricEndEvent();

        if ($cascadeToHistory) {
            $historicIncidentManager = $commandContext->getHistoricIncidentManager();
            $historicIncidentManager->deleteHistoricIncidentsByJobDefinitionId($this->seedJobDefinitionId);
            $historicIncidentManager->deleteHistoricIncidentsByJobDefinitionId($this->monitorJobDefinitionId);
            $historicIncidentManager->deleteHistoricIncidentsByJobDefinitionId($this->batchJobDefinitionId);

            $historicJobLogManager = $commandContext->getHistoricJobLogManager();
            $historicJobLogManager->deleteHistoricJobLogsByJobDefinitionId($this->seedJobDefinitionId);
            $historicJobLogManager->deleteHistoricJobLogsByJobDefinitionId($this->monitorJobDefinitionId);
            $historicJobLogManager->deleteHistoricJobLogsByJobDefinitionId($this->batchJobDefinitionId);

            $commandContext->getHistoricBatchManager()->deleteHistoricBatchById($this->id);
        }
    }

    protected function deleteVariables(CommandContext $commandContext): void
    {
        $variableInstanceManager = $commandContext->getVariableInstanceManager();

        $variableInstances =
            $variableInstanceManager->findVariableInstancesByBatchId($this->id);

        foreach ($variableInstances as $variable) {
            $variable->delete();
        }
    }

    public function fireHistoricStartEvent(): void
    {
        Context::getCommandContext()
            ->getHistoricBatchManager()
            ->createHistoricBatch($this);
    }

    public function fireHistoricEndEvent(): void
    {
        Context::getCommandContext()
            ->getHistoricBatchManager()
            ->completeHistoricBatch($this);
    }

    public function isCompleted(): bool
    {
        return Context::getCommandContext()->getProcessEngineConfiguration()
            ->getManagementService()
            ->createJobQuery()
            ->jobDefinitionId($this->batchJobDefinitionId)
            ->count() == 0;
    }

    public function __toString()
    {
        return "BatchEntity{" .
            "batchHandler=" . $this->batchJobHandler .
            ", id='" . $this->id . '\'' .
            ", type='" . $this->type . '\'' .
            ", size=" . $this->totalJobs .
            ", jobCreated=" . $this->jobsCreated .
            ", batchJobsPerSeed=" . $this->batchJobsPerSeed .
            ", invocationsPerBatchJob=" . $this->invocationsPerBatchJob .
            ", seedJobDefinitionId='" . $this->seedJobDefinitionId . '\'' .
            ", monitorJobDefinitionId='" . $this->seedJobDefinitionId . '\'' .
            ", batchJobDefinitionId='" . $this->batchJobDefinitionId . '\'' .
            ", configurationId='" . $this->configuration->getByteArrayId() . '\'' .
            '}';
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->seedJobDefinitionId !== null) {
            $referenceIdAndClass[$this->seedJobDefinitionId] = JobDefinitionEntity::class;
        }
        if ($this->batchJobDefinitionId !== null) {
            $referenceIdAndClass[$this->batchJobDefinitionId] = JobDefinitionEntity::class;
        }
        if ($this->monitorJobDefinitionId !== null) {
            $referenceIdAndClass[$this->monitorJobDefinitionId] = JobDefinitionEntity::class;
        }

        return $referenceIdAndClass;
    }

    public function getDependentEntities(): array
    {
        return [];
    }
}
