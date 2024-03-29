<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Impl\Event\{
    EventHandlerInterface,
    EventType
};
use Jabe\Impl\JobExecutor\EventSubscriptionJobDeclaration;
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl
};
use Jabe\Impl\Util\{
    ClassNameUtil,
    ClockUtil,
    EnsureUtil
};
use Jabe\Runtime\EventSubscriptionInterface;

class EventSubscriptionEntity implements EventSubscriptionInterface, DbEntityInterface, HasDbRevisionInterface, HasDbReferencesInterface
{
    // persistent state ///////////////////////////
    protected $id;
    protected int $revision = 1;
    protected $eventType;
    protected $eventName;

    protected $executionId;
    protected $processInstanceId;
    protected $activityId;
    protected $configuration;
    protected $created;
    protected $tenantId;

    // runtime state /////////////////////////////
    protected $execution;
    protected $activity;
    protected $jobDeclaration;

    /////////////////////////////////////////////

    //only for mybatis
    public function __construct(?ExecutionEntity $executionEntity = null, ?EventType $eventType = null)
    {
        $this->created = ClockUtil::getCurrentTime()->format('Y-m-d H:i:s');
        $this->eventType = $eventType !== null ? $eventType->name() : null;

        if ($executionEntity !== null) {
            $this->setExecution($executionEntity);
            $this->setActivity($executionEntity->getActivity());
            $this->processInstanceId = $executionEntity->getProcessInstanceId();
            $this->tenantId = $executionEntity->getTenantId();
        }
    }

    // processing /////////////////////////////
    public function eventReceived($payload = null, $payloadLocal = null, ?string $businessKey = null, bool $processAsync = false): void
    {
        if ($processAsync) {
            $this->scheduleEventAsync($payload, $payloadLocal, $businessKey);
        } else {
            $this->processEventSync($payload, $payloadLocal, $businessKey);
        }
    }

    protected function processEventSync($payload = null, $payloadLocal = null, ?string $businessKey = null): void
    {
        $eventHandler = Context::getProcessEngineConfiguration()->getEventHandler($this->eventType);
        EnsureUtil::ensureNotNull("Could not find eventhandler for event of type '" . $this->eventType . "'", "eventHandler", $eventHandler);
        $eventHandler->handleEvent($this, $payload, $payloadLocal, $businessKey, Context::getCommandContext());
    }

    protected function scheduleEventAsync($payload = null, $payloadLocal = null, ?string $businessKey = null): void
    {
        $asyncDeclaration = $this->getJobDeclaration();

        if ($asyncDeclaration === null) {
            // fallback to sync if we couldn't find a job declaration
            $this->processEventSync($payload, $payloadLocal, $businessKey);
        } else {
            $message = $asyncDeclaration->createJobInstance($this);
            $commandContext = Context::getCommandContext();
            $commandContext->getJobManager()->send($message);
        }
    }

    // persistence behavior /////////////////////
    public function delete(): void
    {
        Context::getCommandContext()
            ->getEventSubscriptionManager()
            ->deleteEventSubscription($this);
        $this->removeFromExecution();
    }

    public function insert(): void
    {
        Context::getCommandContext()
            ->getEventSubscriptionManager()
            ->insert($this);
        $this->addToExecution();
    }

    public static function createAndInsert(ExecutionEntity $executionEntity, EventType $eventType, ActivityImpl $activity, ?string $configuration = null): EventSubscriptionEntity
    {
        $eventSubscription = new EventSubscriptionEntity($executionEntity, $eventType);
        $eventSubscription->setActivity($activity);
        $eventSubscription->setTenantId($executionEntity->getTenantId());
        $eventSubscription->setConfiguration($configuration);
        $eventSubscription->insert();
        return $eventSubscription;
    }

   // referential integrity -> ExecutionEntity ////////////////////////////////////

    protected function addToExecution(): void
    {
        // add reference in execution
        $execution = $this->getExecution();
        if ($execution !== null) {
            $execution->addEventSubscription($this);
        }
    }

    protected function removeFromExecution(): void
    {
        // remove reference in execution
        $execution = $this->getExecution();
        if ($execution !== null) {
            $execution->removeEventSubscription($this);
        }
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["executionId"] = $this->executionId;
        $persistentState["configuration"] = $this->configuration;
        $persistentState["activityId"] = $this->activityId;
        $persistentState["eventName"] = $this->eventName;
        return $persistentState;
    }

    // getters & setters ////////////////////////////

    public function getExecution(): ?ExecutionEntity
    {
        if ($this->execution === null && $this->executionId !== null) {
            $this->execution = Context::getCommandContext()
                    ->getExecutionManager()
                    ->findExecutionById($this->executionId);
        }
        return $this->execution;
    }

    public function setExecution(?ExecutionEntity $execution = null): void
    {
        if ($execution !== null) {
            $this->execution = $execution;
            $this->executionId = $execution->getId();
            $this->addToExecution();
        } else {
            $this->removeFromExecution();
            $this->executionId = null;
            $this->execution = null;
        }
    }

    public function getActivity(): ?ActivityImpl
    {
        if ($this->activity === null && $this->activityId !== null) {
            $processDefinition = $this->getProcessDefinition();
            $this->activity = $processDefinition->findActivity($this->activityId);
        }
        return $this->activity;
    }

    public function getProcessDefinition(): ?ProcessDefinitionEntity
    {
        if ($this->executionId !== null) {
            $execution = $this->getExecution();
            return $execution->getProcessDefinition();
        } else {
            // this assumes that start event subscriptions have the process definition id
            // as their configuration (which holds for message and signal start events)
            $processDefinitionId = $this->getConfiguration();
            return Context::getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($processDefinitionId);
        }
    }

    public function setActivity(?ActivityImpl $activity = null): void
    {
        $this->activity = $activity;
        if ($activity !== null) {
            $this->activityId = $activity->getId();
        }
    }

    public function getJobDeclaration(): ?EventSubscriptionJobDeclaration
    {
        if ($this->jobDeclaration === null) {
            $this->jobDeclaration = EventSubscriptionJobDeclaration::findDeclarationForSubscription($this);
        }

        return $this->jobDeclaration;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function isSubscriptionForEventType(EventType $eventType): bool
    {
        return $this->eventType == $eventType->name();
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(?string $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(?string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function setExecutionId(?string $executionId): void
    {
        $this->executionId = $executionId;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(?string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    public function setConfiguration(?string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function setActivityId(?string $activityId): void
    {
        $this->activityId = $activityId;
        $this->activity = null;
    }

    public function getCreated(): ?string
    {
        return $this->created;
    }

    public function setCreated(?string $created): void
    {
        $this->created = $created;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

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

        if (!empty($this->executionId)) {
            $referenceIdAndClass[$this->executionId] = ExecutionEntity::class;
        }

        return $referenceIdAndClass;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
             . "[id=" . $this->id
             . ", eventType=" . $this->eventType
             . ", eventName=" . $this->eventName
             . ", executionId=" . $this->executionId
             . ", processInstanceId=" . $this->processInstanceId
             . ", activityId=" . $this->activityId
             . ", tenantId=" . $this->tenantId
             . ", configuration=" . $this->configuration
             . ", revision=" . $this->revision
             . ", created=" . $this->created
             . "]";
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'eventType' => $this->eventType,
            'eventName' => $this->eventName,
            'executionId' => $this->executionId,
            'processInstanceId' => $this->processInstanceId,
            'activityId' => $this->activityId,
            'tenantId' => $this->tenantId,
            'configuration' => $this->configuration,
            'revision' => $this->revision,
            'created' => $this->created
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->eventType = $data['eventType'];
        $this->eventName = $data['eventName'];
        $this->executionId = $data['executionId'];
        $this->processInstanceId = $data['processInstanceId'];
        $this->activityId = $data['activityId'];
        $this->tenantId = $data['tenantId'];
        $this->configuration = $data['configuration'];
        $this->revision = $data['revision'];
        $this->created = $data['created'];
    }

    public function getDependentEntities(): array
    {
        return [];
    }
}
