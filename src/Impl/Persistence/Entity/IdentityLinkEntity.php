<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface
};
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypeInterface,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Task\IdentityLinkInterface;
use Jabe\Impl\Util\ClassNameUtil;

class IdentityLinkEntity implements \Serializable, IdentityLinkInterface, DbEntityInterface, HasDbReferencesInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $id;

    protected $type;

    protected $userId;

    protected $groupId;

    protected $taskId;

    protected $processDefId;

    protected $tenantId;

    protected $task;

    protected $processDef;

    public function getPersistentState()
    {
        return $this->type;
    }

    public static function createAndInsert(): IdentityLinkEntity
    {
        $identityLinkEntity = new IdentityLinkEntity();
        $identityLinkEntity->insert();
        return $identityLinkEntity;
    }

    public static function newIdentityLink(): IdentityLinkEntity
    {
        $identityLinkEntity = new IdentityLinkEntity();
        return $identityLinkEntity;
    }

    public function insert(): void
    {
        Context::getCommandContext()
            ->getDbEntityManager()
            ->insert($this);
        $this->fireHistoricIdentityLinkEvent(HistoryEventTypes::identityLinkAdd());
    }

    public function delete(?bool $withHistory = true): void
    {
        Context::getCommandContext()
            ->getDbEntityManager()
            ->delete($this);
        if ($withHistory) {
            $this->fireHistoricIdentityLinkEvent(HistoryEventTypes::identityLinkDelete());
        }
    }

    public function isUser(): bool
    {
        return $this->userId !== null;
    }

    public function isGroup(): bool
    {
        return $this->groupId !== null;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        if ($this->groupId !== null && $userId !== null) {
            //throw LOG.taskIsAlreadyAssignedException("userId", "groupId");
            throw new \Exception("IdentityLinkEntity exception");
        }
        $this->userId = $userId;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): void
    {
        if ($this->userId !== null && $groupId !== null) {
            //throw LOG.taskIsAlreadyAssignedException("groupId", "userId");
            throw new \Exception("IdentityLinkEntity exception");
        }
        $this->groupId = $groupId;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setTaskId(?string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getProcessDefId(): ?string
    {
        return $this->processDefId;
    }

    public function setProcessDefId(?string $processDefId): void
    {
        $this->processDefId = $processDefId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getTask(): ?TaskEntity
    {
        if (($this->task === null) && ($this->taskId !== null)) {
            $this->task = Context::getCommandContext()
            ->getTaskManager()
            ->findTaskById($this->taskId);
        }
        return $this->task;
    }

    public function setTask(TaskEntity $task): void
    {
        $this->task = $task;
        $this->taskId = $task->getId();
    }

    public function getProcessDef(): ?ProcessDefinitionEntity
    {
        if (($this->processDef === null) && ($this->processDefId !== null)) {
            $this->processDef = Context::getCommandContext()
                    ->getProcessDefinitionManager()
                    ->findLatestProcessDefinitionById($this->processDefId);
        }
        return $this->processDef;
    }

    public function setProcessDef(ProcessDefinitionEntity $processDef): void
    {
        $this->processDef = $processDef;
        $this->processDefId = $processDef->getId();
    }

    public function fireHistoricIdentityLinkEvent(HistoryEventTypeInterface $eventType): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        $historyLevel = $processEngineConfiguration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced($eventType, $this)) {
            $scope = $this;
            HistoryEventProcessor::processHistoryEvents(new class ($scope, $eventType) extends HistoryEventCreator {
                private $scope;

                private $eventType;

                public function __construct(IdentityLinkEntity $scope, HistoryEventTypeInterface $eventType)
                {
                    $this->scope = $scope;
                    $this->eventType = $eventType;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    $event = null;
                    if (HistoryEvent::identityLinkAdd() == $this->eventType->getEventName()) {
                        $event = $producer->createHistoricIdentityLinkAddEvent($this->scope);
                    } elseif (HistoryEvent::identityLinkDelete() == $this->eventType->getEventName()) {
                        $event = $producer->createHistoricIdentityLinkDeleteEvent($this->scope);
                    }
                    return $event;
                }
            });
        }
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->processDefId !== null) {
            $referenceIdAndClass[$this->processDefId] = ProcessDefinitionEntity::class;
        }
        if ($this->taskId !== null) {
            $referenceIdAndClass[$this->taskId] = TaskEntity::class;
        }

        return $referenceIdAndClass;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'type' => $this->type,
            'userId' => $this->userId,
            'groupId' => $this->groupId,
            'taskId' => $this->taskId,
            'processDefId' => $this->processDefId,
            'tenantId' => $this->tenantId,
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->userId = $json->userId;
        $this->groupId = $json->groupId;
        $this->taskId = $json->taskId;
        $this->processDefId = $json->processDefId;
        $this->tenantId = $json->tenantId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", type=" . $this->type
                . ", userId=" . $this->userId
                . ", groupId=" . $this->groupId
                . ", taskId=" . $this->taskId
                . ", processDefId=" . $this->processDefId
                . ", task=" . $this->task
                . ", processDef=" . $this->processDef
                . ", tenantId=" . $this->tenantId
                . "]";
    }

    public function getDependentEntities(): array
    {
        return [];
    }
}
