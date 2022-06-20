<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Engine\Impl\History\HistoryLevel;
use Jabe\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypeInterface,
    HistoryEventTypes
};
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Engine\Impl\Incident\{
    IncidentContext,
    IncidentLogger
};
use Jabe\Engine\Impl\Util\ClockUtil;
use Jabe\Engine\Runtime\IncidentInterface;
use Jabe\Engine\Impl\Util\ClassNameUtil;

class IncidentEntity implements IncidentInterface, DbEntityInterface, HasDbRevisionInterface, HasDbReferencesInterface
{
    //protected static final IncidentLogger LOG = ProcessEngineLogger.INCIDENT_LOGGER;

    protected $revision;

    protected $id;
    protected $incidentTimestamp;
    protected $incidentType;
    protected $executionId;
    protected $activityId;
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $causeIncidentId;
    protected $rootCauseIncidentId;
    protected $configuration;
    protected $incidentMessage;
    protected $tenantId;
    protected $jobDefinitionId;
    protected $historyConfiguration;
    protected $failedActivityId;
    protected $annotation;

    /** Instantiate recursive a new incident a super execution
     * (i.e. super process instance) which is affected from this
     * incident.
     * For example: a super process instance called via CallActivity
     * a new process instance on which an incident happened, so that
     * the super process instance has an incident too. */
    protected function createRecursiveIncidents(?string $rootCauseIncidentId = null, array $createdIncidents = []): array
    {
        $rootCauseIncidentId = $rootCauseIncidentId ?? $this->id;
        $execution = getExecution();

        if ($execution != null) {
            $superExecution = $execution->getProcessInstance()->getSuperExecution();

            if ($superExecution != null) {
                // create a new incident
                $newIncident = $this->create($incidentType);
                $newIncident->setExecution($superExecution);
                $newIncident->setActivityId($superExecution->getCurrentActivityId());
                $newIncident->setFailedActivityId($superExecution->getCurrentActivityId());
                $newIncident->setProcessDefinitionId($superExecution->getProcessDefinitionId());
                $newIncident->setTenantId($superExecution->getTenantId());

                // set cause and root cause
                $newIncident->setCauseIncidentId($id);
                $newIncident->setRootCauseIncidentId($rootCauseIncidentId);

                // insert new incident (and create a new historic incident)
                $this->insert($newIncident);

                // add new incident to result set
                $createdIncidents[] = $newIncident;

                $newIncident->createRecursiveIncidents($rootCauseIncidentId, $createdIncidents);
            }
        }
        return $createdIncidents;
    }

    public static function createAndInsertIncident(string $incidentType, IncidentContext $context, string $message): IncidentEntity
    {
        // create new incident
        $newIncident = $this->create($incidentType);
        $newIncident->setIncidentMessage($message);

        // set properties from incident context
        $newIncident->setConfiguration($context->getConfiguration());
        $newIncident->setActivityId($context->getActivityId());
        $newIncident->setProcessDefinitionId($context->getProcessDefinitionId());
        $newIncident->setTenantId($context->getTenantId());
        $newIncident->setJobDefinitionId($context->getJobDefinitionId());
        $newIncident->setHistoryConfiguration($context->getHistoryConfiguration());
        $newIncident->setFailedActivityId($context->getFailedActivityId());

        if ($context->getExecutionId() != null) {
            // fetch execution
            $execution = Context::getCommandContext()
            ->getExecutionManager()
            ->findExecutionById($context->getExecutionId());

            if ($execution != null) {
                // link incident with execution
                $newIncident->setExecution($execution);
            } else {
              //LOG.executionNotFound(context->getExecutionId());
            }
        }

        // insert new incident (and create a new historic incident)
        $this->insert($newIncident);

        return $newIncident;
    }

    protected static function create(string $incidentType): IncidentEntity
    {
        $incidentId = Context::getProcessEngineConfiguration()
            ->getDbSqlSessionFactory()
            ->getIdGenerator()
            ->getNextId();

        // decorate new incident
        $newIncident = new IncidentEntity();
        $newIncident->setId($incidentId);
        $newIncident->setIncidentTimestamp(ClockUtil::getCurrentTime()->format('c'));
        $newIncident->setIncidentType($incidentType);
        $newIncident->setCauseIncidentId($incidentId);
        $newIncident->setRootCauseIncidentId($incidentId);

        return $newIncident;
    }

    protected static function insert(IncidentEntity $incident): void
    {
        // persist new incident
        Context::getCommandContext()
          ->getDbEntityManager()
          ->insert($incident);

        $incident->fireHistoricIncidentEvent(HistoryEventTypes::incidentCreate());
    }

    public function delete(): void
    {
        $this->remove(false);
    }

    public function resolve(): void
    {
        $this->remove(true);
    }

    protected function remove(bool $resolved): void
    {
        $execution = $this->getExecution();

        if ($execution != null) {
            // Extract possible super execution of the assigned execution
            $superExecution = null;
            if ($execution->getId() == $execution->getProcessInstanceId()) {
                $superExecution = $execution->getSuperExecution();
            } else {
                $superExecution = $execution->getProcessInstance()->getSuperExecution();
            }

            if ($superExecution != null) {
                // get the incident, where this incident is the cause
                $parentIncident = $superExecution->getIncidentByCauseIncidentId($this->getId());

                if ($parentIncident != null) {
                    // remove the incident
                    $parentIncident->remove($resolved);
                }
            }
            // remove link to execution
            $execution->removeIncident($this);
        }

        // always delete the incident
        Context::getCommandContext()
          ->getDbEntityManager()
          ->delete($this);

        // update historic incident
        $eventType = $resolved ? HistoryEventTypes::incidentResolve() : HistoryEventTypes::incidentDelete();
        $this->fireHistoricIncidentEvent($eventType);
    }

    protected function fireHistoricIncidentEvent(HistoryEventTypeInterface $eventType): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        $historyLevel = $processEngineConfiguration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced($eventType, $this)) {
            $scope = $this;
            HistoryEventProcessor::processHistoryEvents(new class ($scope, $eventType) extends HistoryEventCreator {
                private $scope;
                private $eventType;

                public function __construct(IncidentEntity $scope, HistoryEventTypeInterface $eventType)
                {
                    $this->scope = $scope;
                    $this->eventType = $eventType;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    $event = null;
                    if (HistoryEventTypes::incidentCreate()->getEventName() == $this->eventType->getEventName()) {
                        $event = $producer->createHistoricIncidentCreateEvt($this->scope);
                    } elseif (HistoryEventTypes::incidentResolve()->getEventName() == $this->eventType->getEventName()) {
                        $event = $producer->createHistoricIncidentResolveEvt($this->scope);
                    } elseif (HistoryEventTypes::incidentDelete()->getEventName() == $this->eventType->getEventName()) {
                        $event = $producer->createHistoricIncidentDeleteEvt($this->scope);
                    }
                    return $event;
                }
            });
        }
    }

    public function getReferencedEntityIds(): array
    {
        $referenceIds = [];

        if ($this->causeIncidentId != null) {
            $referenceIds[] = $this->causeIncidentId;
        }

        return $referenceIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->causeIncidentId != null) {
            $referenceIdAndClass[$this->causeIncidentId] = IncidentEntity::class;
        }
        if ($this->processDefinitionId != null) {
            $referenceIdAndClass[$this->processDefinitionId] = ProcessDefinitionEntity::class;
        }
        if ($this->processInstanceId != null) {
            $referenceIdAndClass[$this->processInstanceId] = ExecutionEntity::class;
        }
        if ($this->jobDefinitionId != null) {
            $referenceIdAndClass[$this->jobDefinitionId] = JobDefinitionEntity::class;
        }
        if ($this->executionId != null) {
            $referenceIdAndClass[$this->executionId] = ExecutionEntity::class;
        }
        if ($this->rootCauseIncidentId != null) {
            $referenceIdAndClass[$this->rootCauseIncidentId] = IncidentEntity::class;
        }

        return $referenceIdAndClass;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getIncidentTimestamp(): string
    {
        return $this->incidentTimestamp;
    }

    public function setIncidentTimestamp(string $incidentTimestamp): void
    {
        $this->incidentTimestamp = $incidentTimestamp;
    }

    public function getIncidentType(): string
    {
        return $this->incidentType;
    }

    public function setIncidentType(string $incidentType): void
    {
        $this->incidentType = $incidentType;
    }

    public function getIncidentMessage(): string
    {
        return $this->incidentMessage;
    }

    public function setIncidentMessage(string $incidentMessage): void
    {
        $this->incidentMessage = $incidentMessage;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getProcessDefinition(): ?ProcessDefinitionEntity
    {
        if ($this->processDefinitionId != null) {
            return Context::getProcessEngineConfiguration()
                ->getDeploymentCache()
                ->findDeployedProcessDefinitionById($this->processDefinitionId);
        }
        return null;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getCauseIncidentId(): string
    {
        return $this->causeIncidentId;
    }

    public function setCauseIncidentId(string $causeIncidentId): void
    {
        $this->causeIncidentId = $causeIncidentId;
    }

    public function getRootCauseIncidentId(): string
    {
        return $this->rootCauseIncidentId;
    }

    public function setRootCauseIncidentId(string $rootCauseIncidentId): void
    {
        $this->rootCauseIncidentId = $rootCauseIncidentId;
    }

    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function setJobDefinitionId(string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    public function setExecution(ExecutionEntity $execution): void
    {
        $oldExecution = $this->getExecution();
        if ($oldExecution != null) {
            $oldExecution->removeIncident($this);
        }

        if ($execution != null) {
            $this->executionId = $execution->getId();
            $this->processInstanceId = $execution->getProcessInstanceId();
            $execution->addIncident($this);
        } else {
            $this->executionId = null;
            $this->processInstanceId = null;
        }
    }

    public function getExecution(): ?ExecutionEntity
    {
        if ($this->executionId != null) {
            $execution = Context::getCommandContext()
            ->getExecutionManager()
            ->findExecutionById($this->executionId);

            if ($execution == null) {
                //LOG.executionNotFound(executionId);
            }

            return $execution;
        } else {
            return null;
        }
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["executionId"] = $this->executionId;
        $persistentState["processDefinitionId"] = $this->processDefinitionId;
        $persistentState["activityId"] = $this->activityId;
        $persistentState["jobDefinitionId"] = $this->jobDefinitionId;
        $persistentState["annotation"] = $this->annotation;
        return $persistentState;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getHistoryConfiguration(): string
    {
        return $this->historyConfiguration;
    }

    public function setHistoryConfiguration(string $historyConfiguration): void
    {
        $this->historyConfiguration = $historyConfiguration;
    }

    public function getFailedActivityId(): ?string
    {
        return $this->failedActivityId;
    }

    public function setFailedActivityId(string $failedActivityId): void
    {
        $this->failedActivityId = $failedActivityId;
    }

    public function getAnnotation(): string
    {
        return $this->annotation;
    }

    public function setAnnotation(string $annotation): void
    {
        $this->annotation = $annotation;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'incidentTimestamp' => $this->incidentTimestamp,
            'incidentType' => $this->incidentType,
            'executionId' => $this->executionId,
            'activityId' => $this->activityId,
            'processInstanceId' => $this->processInstanceId,
            'processDefinitionId' => $this->processDefinitionId,
            'causeIncidentId' => $this->causeIncidentId,
            'rootCauseIncidentId' => $this->rootCauseIncidentId,
            'configuration' => $this->configuration,
            'tenantId' => $this->tenantId,
            'incidentMessage' => $this->incidentMessage,
            'jobDefinitionId' => $this->jobDefinitionId,
            'failedActivityId' => $this->failedActivityId,
            'annotation' => $this->annotation,
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->incidentTimestamp = $json->incidentTimestamp;
        $this->incidentType = $json->incidentType;
        $this->executionId = $json->executionId;
        $this->activityId = $json->activityId;
        $this->processInstanceId = $json->processInstanceId;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->causeIncidentId = $json->causeIncidentId;
        $this->rootCauseIncidentId = $json->rootCauseIncidentId;
        $this->configuration = $json->configuration;
        $this->tenantId = $json->tenantId;
        $this->incidentMessage = $json->incidentMessage;
        $this->jobDefinitionId = $json->jobDefinitionId;
        $this->failedActivityId = $json->failedActivityId;
        $this->annotation = $json->annotation;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", incidentTimestamp=" . $this->incidentTimestamp
                . ", incidentType=" . $this->incidentType
                . ", executionId=" . $this->executionId
                . ", activityId=" . $this->activityId
                . ", processInstanceId=" . $this->processInstanceId
                . ", processDefinitionId=" . $this->processDefinitionId
                . ", causeIncidentId=" . $this->causeIncidentId
                . ", rootCauseIncidentId=" . $this->rootCauseIncidentId
                . ", configuration=" . $this->configuration
                . ", tenantId=" . $this->tenantId
                . ", incidentMessage=" . $this->incidentMessage
                . ", jobDefinitionId=" . $this->jobDefinitionId
                . ", failedActivityId=" . $this->failedActivityId
                . ", annotation=" . $this->annotation
                . "]";
    }

    public function equals($obj = null): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj == null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->id == null) {
            if ($obj->id != null) {
                return false;
            }
        } elseif ($this->id != $obj->id) {
            return false;
        }
        return true;
    }
}
