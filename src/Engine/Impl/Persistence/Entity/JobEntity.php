<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Util\{
    EnsureUtil,
    ExceptionUtil,
    StringUtil
};
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Engine\Impl\Incident\{
    IncidentContext,
    IncidentHandling
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\JobExecutor\{
    DefaultJobPriorityProvider,
    JobHandlerInterface,
    JobHandlerConfigurationInterface
};
use Jabe\Engine\Management\JobDefinitionInterface;
use Jabe\Engine\Repository\ResourceTypes;
use Jabe\Engine\Runtime\{
    IncidentInterface,
    JobInterface
};
use Jabe\Engine\Impl\Util\ClassNameUtil;

abstract class JobEntity extends AcquirableJobEntity implements \Serializable, HasDbReferencesInterface, JobInterface, DbEntityInterface, HasDbRevisionInterface, DbEntityLifecycleAwareInterface
{
    //private final static EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public const DEFAULT_RETRIES = 3;

    protected $executionId = null;

    protected $processDefinitionId = null;
    protected $processDefinitionKey = null;

    protected $retries = self::DEFAULT_RETRIES;

    // entity is active by default
    protected $suspensionState = 1;

    protected $jobHandlerType = null;
    protected $jobHandlerConfiguration = null;

    protected $exceptionByteArray;
    protected $exceptionByteArrayId;

    protected $exceptionMessage;

    protected $deploymentId;

    protected $jobDefinitionId;

    protected $priority = DefaultJobPriorityProvider::DEFAULT_PRIORITY;

    protected $tenantId;

    protected $createTime;

    // runtime state /////////////////////////////
    protected $activityId;
    protected $jobDefinition;
    protected $execution;

    // sequence counter //////////////////////////
    protected $sequenceCounter = 1;

    // last failure log id ///////////////////////
    protected $lastFailureLogId;

    // last failing activity id ///////////////////////
    protected $failedActivityId;

    protected $persistedDependentEntities;

    public function execute(CommandContext $commandContext)
    {
        if ($this->executionId !== null) {
            $execution = $this->getExecution();
            EnsureUtil::ensureNotNull("Cannot find execution with id '" . $this->executionId . "' referenced from job '" . $this . "'", "execution", $execution);
        }

        // initialize activity id
        $this->getActivityId();

        // increment sequence counter before job execution
        $this->incrementSequenceCounter();

        $this->preExecute($commandContext);
        $jobHandler = $this->getJobHandler();
        $configuration = $this->getJobHandlerConfiguration();
        EnsureUtil::ensureNotNull("Cannot find job handler '" . $jobHandlerType . "' from job '" . $this . "'", "jobHandler", $jobHandler);
        $jobHandler->execute($configuration, $execution, $commandContext, $tenantId);
        $this->postExecute($commandContext);
    }

    protected function preExecute(CommandContext $commandContext): void
    {
        // nothing to do
    }

    protected function postExecute(CommandContext $commandContext): void
    {
        //LOG.debugJobExecuted(this);
        $this->delete(true);
        $commandContext->getHistoricJobLogManager()->fireJobSuccessfulEvent($this);
    }

    public function init(CommandContext $commandContext): void
    {
        // nothing to do
    }

    public function insert(): void
    {
        $commandContext = Context::getCommandContext();

        // add link to execution and deployment
        $execution = $this->getExecution();
        if ($execution !== null) {
            $execution->addJob($this);

            $processDefinition = $execution->getProcessDefinition();
            $this->deploymentId = $processDefinition->getDeploymentId();
        }

        $commandContext
        ->getJobManager()
        ->insertJob($this);
    }

    public function delete(?bool $incidentResolved = false): void
    {
        $commandContext = Context::getCommandContext();

        $this->incrementSequenceCounter();

        // clean additional data related to this job
        $jobHandler = $this->getJobHandler();
        if ($jobHandler !== null) {
            $jobHandler->onDelete($this->getJobHandlerConfiguration(), $this);
        }

        // fire delete event if this job is not being executed
        $executingJob = $this->equals($commandContext->getCurrentJob());
        $commandContext->getJobManager()->deleteJob($this, !$executingJob);

        // Also delete the job's exception byte array
        if ($this->exceptionByteArrayId !== null) {
            $commandContext->getByteArrayManager()->deleteByteArrayById($this->exceptionByteArrayId);
        }

        // remove link to execution
        $execution = $this->getExecution();
        if ($execution !== null) {
            $execution->removeJob($this);
        }

        $this->removeFailedJobIncident($incidentResolved);
    }

    public function getPersistentState()
    {
        $persistentState = parent::getPersistentState();
        $persistentState["executionId"] = $this->executionId;
        $persistentState["retries"] = $this->retries;
        $persistentState["exceptionMessage"] = $this->exceptionMessage;
        $persistentState["suspensionState"] = $this->suspensionState;
        $persistentState["processDefinitionId"] = $this->processDefinitionId;
        $persistentState["jobDefinitionId"] = $this->jobDefinitionId;
        $persistentState["deploymentId"] = $this->deploymentId;
        $persistentState["jobHandlerConfiguration"] = $this->jobHandlerConfiguration;
        $persistentState["priority"] = $this->priority;
        $persistentState["tenantId"] = $this->tenantId;
        if ($this->exceptionByteArrayId !== null) {
            $persistentState["exceptionByteArrayId"] = $exceptionByteArrayId;
        }
        return $persistentState;
    }

    public function setExecution(ExecutionEntity $execution): void
    {
        if ($execution !== null) {
            $this->execution = $execution;
            $this->executionId = $execution->getId();
            $this->processInstanceId = $execution->getProcessInstanceId();
            $this->execution->addJob($this);
        } else {
            $this->execution->removeJob($this);
            $this->execution = $execution;
            $this->processInstanceId = null;
            $this->executionId = null;
        }
    }

    // sequence counter /////////////////////////////////////////////////////////

    public function getSequenceCounter(): int
    {
        return $this->sequenceCounter;
    }

    public function setSequenceCounter(int $sequenceCounter): void
    {
        $this->sequenceCounter = $sequenceCounter;
    }

    public function incrementSequenceCounter(): void
    {
        $this->sequenceCounter += 1;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
    }

    public function getExecution(): ?ExecutionEntity
    {
        $this->ensureExecutionInitialized();
        return $this->execution;
    }

    protected function ensureExecutionInitialized(): void
    {
        if ($this->execution === null && $this->executionId !== null) {
            $this->execution = Context::getCommandContext()
                ->getExecutionManager()
                ->findExecutionById($this->executionId);
        }
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function setRetries(int $retries): void
    {
        // if retries should be set to a negative value set it to 0
        if ($retries < 0) {
            $retries = 0;
        }

        // Assuming: if the number of retries will
        // be changed from 0 to x (x >= 1), means
        // that the corresponding incident is resolved.
        if ($this->retries == 0 && $retries > 0) {
            $this->removeFailedJobIncident(true);
        }

        // If the retries will be set to 0, an
        // incident has to be created.
        if ($retries == 0 && $this->retries > 0) {
            $this->createFailedJobIncident();
        }
        $this->retries = $retries;
    }

    // special setter for MyBatis which does not influence incidents
    public function setRetriesFromPersistence(int $retries): void
    {
        $this->retries = $retries;
    }

    protected function createFailedJobIncident(): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        if ($processEngineConfiguration->isCreateIncidentOnFailedJobEnabled()) {
            $incidentHandlerType = IncidentInterface::FAILED_JOB_HANDLER_TYPE;

            // make sure job has an ID set:
            if ($this->id === null) {
                $this->id = $processEngineConfiguration
                    ->getIdGenerator()
                    ->getNextId();
            } else {
                // check whether there exists already an incident
                // for this job
                $failedJobIncidents = Context::getCommandContext()
                    ->getIncidentManager()
                    ->findIncidentByConfigurationAndIncidentType($this->id, $incidentHandlerType);

                if (!empty($failedJobIncidents)) {
                    // update the historic job log id in the historic incidents (if available)
                    foreach ($failedJobIncidents as $incident) {
                        $historicIncidentEvent = Context::getCommandContext()
                            ->getHistoricIncidentManager()
                            ->findHistoricIncidentById($incident->getId());
                        if ($historicIncidentEvent !== null) {
                            $historicIncidentEvent->setHistoryConfiguration($this->getLastFailureLogId());
                            Context::getCommandContext()->getDbEntityManager()->merge($historicIncidentEvent);
                        }
                    }
                    return;
                }
            }
            $incidentContext = $this->createIncidentContext();
            $incidentContext->setActivityId($this->getActivityId());
            $incidentContext->setHistoryConfiguration($this->getLastFailureLogId());
            $incidentContext->setFailedActivityId($this->getFailedActivityId());
            IncidentHandling::createIncident($incidentHandlerType, $incidentContext, $this->exceptionMessage);
        }
    }

    protected function removeFailedJobIncident(bool $incidentResolved): void
    {
        $incidentContext = $this->createIncidentContext();
        IncidentHandling::removeIncidents(IncidentInterface::FAILED_JOB_HANDLER_TYPE, $incidentContext, $incidentResolved);
    }

    protected function createIncidentContext(): IncidentContext
    {
        $incidentContext = new IncidentContext();
        $incidentContext->setProcessDefinitionId($this->processDefinitionId);
        $incidentContext->setExecutionId($this->executionId);
        $incidentContext->setTenantId($this->tenantId);
        $incidentContext->setConfiguration($this->id);
        $incidentContext->setJobDefinitionId($this->jobDefinitionId);

        return $incidentContext;
    }

    public function getExceptionStacktrace(): string
    {
        $byteArray = $this->getExceptionByteArray();
        return ExceptionUtil::getExceptionStacktrace($byteArray);
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
        return $suspensionState == SuspensionState::suspended()->getStateCode();
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function setExceptionStacktrace(string $exception): void
    {
        $exceptionBytes = $exception;

        $byteArray = $this->getExceptionByteArray();

        if ($byteArray === null) {
            $byteArray = ExceptionUtil::createJobExceptionByteArray($exceptionBytes, ResourceTypes::runtime());
            $this->exceptionByteArrayId = $byteArray->getId();
            $this->exceptionByteArray = $byteArray;
        } else {
            $byteArray->setBytes($exceptionBytes);
        }
    }

    protected function getJobHandler(): ?JobHandlerInterface
    {
        $jobHandlers = Context::getProcessEngineConfiguration()->getJobHandlers();
        if (array_key_exists($this->jobHandlerType, $jobHandlers)) {
            return $jobHandlers[$this->jobHandlerType];
        }
        return null;
    }

    public function getJobHandlerConfiguration(): ?JobHandlerConfigurationInterface
    {
        $handler = $this->getJobHandler();
        if ($handler !== null) {
            return $handler->newConfiguration($this->jobHandlerConfiguration);
        }
        return null;
    }

    public function setJobHandlerConfiguration(JobHandlerConfigurationInterface $configuration): void
    {
        $this->jobHandlerConfiguration = $configuration->toCanonicalString();
    }

    public function getJobHandlerType(): string
    {
        return $this->jobHandlerType;
    }

    public function setJobHandlerType(string $jobHandlerType): void
    {
        $this->jobHandlerType = $jobHandlerType;
    }

    public function getJobHandlerConfigurationRaw(): string
    {
        return $this->jobHandlerConfiguration;
    }

    public function setJobHandlerConfigurationRaw(string $jobHandlerConfiguration): void
    {
        $this->jobHandlerConfiguration = $jobHandlerConfiguration;
    }

    public function getExceptionMessage(): ?string
    {
        return $this->exceptionMessage;
    }

    public function getJobDefinitionId(): ?string
    {
        return $this->jobDefinitionId;
    }

    public function setJobDefinitionId(string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function getJobDefinition(): ?JobDefinitionInterface
    {
        $this->ensureJobDefinitionInitialized();
        return $this->jobDefinition;
    }

    public function setJobDefinition(?JobDefinitionInterface $jobDefinition): void
    {
        $this->jobDefinition = $jobDefinition;
        if ($jobDefinition !== null) {
            $this->jobDefinitionId = $jobDefinition->getId();
        } else {
            $this->jobDefinitionId = null;
        }
    }

    protected function ensureJobDefinitionInitialized(): void
    {
        if ($this->jobDefinition === null && $this->jobDefinitionId !== null) {
            $this->jobDefinition = Context::getCommandContext()
                ->getJobDefinitionManager()
                ->findById($this->jobDefinitionId);
        }
    }

    public function setExceptionMessage(string $exceptionMessage): void
    {
        $this->exceptionMessage = StringUtil::trimToMaximumLengthAllowed($exceptionMessage);
    }

    public function getExceptionByteArrayId(): ?string
    {
        return $this->exceptionByteArrayId;
    }

    protected function getExceptionByteArray(): ?ByteArrayEntity
    {
        $this->ensureExceptionByteArrayInitialized();
        return $this->exceptionByteArray;
    }

    protected function ensureExceptionByteArrayInitialized(): void
    {
        if ($this->exceptionByteArray === null && $this->exceptionByteArrayId !== null) {
            $this->exceptionByteArray = Context::getCommandContext()
                ->getDbEntityManager()
                ->selectById(ByteArrayEntity::class, $this->exceptionByteArrayId);
        }
    }

    protected function clearFailedJobException(): void
    {
        $byteArray = $this->getExceptionByteArray();

        // Avoid NPE when the job was reconfigured by another
        // node in the meantime
        if ($byteArray !== null) {
            Context::getCommandContext()
                ->getDbEntityManager()
                ->delete($byteArray);
        }

        $this->exceptionByteArrayId = null;
        $this->exceptionMessage = null;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $this->deploymentId;
    }

    public function isInInconsistentLockState(): bool
    {
        return ($this->lockOwner !== null && $this->lockExpirationTime === null)
            || ($this->retries == 0 && ($this->lockOwner !== null || $this->lockExpirationTime !== null));
    }

    public function resetLock(): void
    {
        $this->lockOwner = null;
        $this->lockExpirationTime = null;
    }

    public function getActivityId(): string
    {
        $this->ensureActivityIdInitialized();
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): void
    {
        $this->createTime = $createTime;
    }

    protected function ensureActivityIdInitialized(): void
    {
        if ($this->activityId === null) {
            $jobDefinition = $this->getJobDefinition();
            if ($jobDefinition !== null) {
                $this->activityId = $jobDefinition->getActivityId();
            } else {
                $execution = $this->getExecution();
                if ($execution !== null) {
                    $this->activityId = $execution->getActivityId();
                }
            }
        }
    }

    /**
     *
     * Unlock from current lock owner
     *
     */
    public function unlock(): void
    {
        $this->lockOwner = null;
        $this->lockExpirationTime = null;
    }

    abstract public function getType(): string;

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->id === null) {
            if ($obj->id !== null) {
                return false;
            }
        } elseif ($this->id != $obj->id) {
            return false;
        }
        return true;
    }

    public function getReferencedEntityIds(): array
    {
        return [];
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->exceptionByteArrayId !== null) {
            $referenceIdAndClass[$this->exceptionByteArrayId] = ByteArrayEntity::class;
        }

        return $referenceIdAndClass;
    }

    public function getDependentEntities(): array
    {
        return $this->persistedDependentEntities;
    }

    public function postLoad(): void
    {
        if ($this->exceptionByteArrayId !== null) {
            $this->persistedDependentEntities = [];
            $this->persistedDependentEntities[$this->exceptionByteArrayId] = ByteArrayEntity::class;
        } else {
            $this->persistedDependentEntities = [];
        }
    }

    public function getLastFailureLogId(): ?string
    {
        return $this->lastFailureLogId;
    }

    public function setLastFailureLogId(string $lastFailureLogId): void
    {
        $this->lastFailureLogId = $lastFailureLogId;
    }

    public function getFailedActivityId(): ?string
    {
        return $this->failedActivityId;
    }

    public function setFailedActivityId(string $failedActivityId): void
    {
        $this->failedActivityId = $failedActivityId;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'revision' => $this->revision,
            'duedate' => $this->duedate,
            'lockOwner' => $this->lockOwner,
            'lockExpirationTime' => $this->lockExpirationTime,
            'executionId' => $this->executionId,
            'processInstanceId' => $this->processInstanceId,
            'isExclusive' => $this->isExclusive,
            'jobDefinitionId' => $this->jobDefinitionId,
            'jobHandlerType' => $this->jobHandlerType,
            'jobHandlerConfiguration' => $this->jobHandlerConfiguration,
            'exceptionByteArray' => serialize($this->exceptionByteArray),
            'exceptionByteArrayId' => $this->exceptionByteArrayId,
            'exceptionMessage' => $this->exceptionMessage,
            'failedActivityId' => $this->failedActivityId,
            'deploymentId' => $this->deploymentId,
            'priority' => $this->priority,
            'tenantId' => $this->tenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->lockOwner = $json->lockOwner;
        $this->lockExpirationTime = $json->lockExpirationTime;
        $this->executionId = $json->executionId;
        $this->processInstanceId = $json->processInstanceId;
        $this->isExclusive = $json->isExclusive;
        $this->jobDefinitionId = $json->jobDefinitionId;
        $this->jobHandlerType = $json->jobHandlerType;
        $this->jobHandlerConfiguration = $json->jobHandlerConfiguration;
        $this->exceptionByteArray = unserialize($json->exceptionByteArray);
        $this->exceptionByteArrayId = $json->exceptionByteArrayId;
        $this->exceptionMessage = $json->exceptionMessage;
        $this->failedActivityId = $json->failedActivityId;
        $this->deploymentId = $json->deploymentId;
        $this->priority = $json->priority;
        $this->tenantId = $json->tenantId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[id=" . $this->id
            . ", revision=" . $this->revision
            . ", duedate=" . $this->duedate
            . ", lockOwner=" . $this->lockOwner
            . ", lockExpirationTime=" . $this->lockExpirationTime
            . ", executionId=" . $this->executionId
            . ", processInstanceId=" . $this->processInstanceId
            . ", isExclusive=" . $this->isExclusive
            . ", jobDefinitionId=" . $this->jobDefinitionId
            . ", jobHandlerType=" . $this->jobHandlerType
            . ", jobHandlerConfiguration=" . $this->jobHandlerConfiguration
            . ", exceptionByteArray=" . $this->exceptionByteArray
            . ", exceptionByteArrayId=" . $this->exceptionByteArrayId
            . ", exceptionMessage=" . $this->exceptionMessage
            . ", failedActivityId=" . $this->failedActivityId
            . ", deploymentId=" . $this->deploymentId
            . ", priority=" . $this->priority
            . ", tenantId=" . $this->tenantId
            . "]";
    }
}
