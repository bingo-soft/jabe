<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Delegate\ExpressionInterface;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbRevisionInterface
};
use Jabe\Impl\Form\FormDefinition;
use Jabe\Impl\Form\Handler\StartFormHandlerInterface;
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl
};
use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;
use Jabe\Impl\Repository\ResourceDefinitionEntityInterface;
use Jabe\Impl\Task\TaskDefinition;
use Jabe\Repository\ProcessDefinitionInterface;
use Jabe\Task\IdentityLinkType;

class ProcessDefinitionEntity extends ProcessDefinitionImpl implements ProcessDefinitionInterface, ResourceDefinitionEntityInterface, DbEntityInterface, HasDbRevisionInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $key;
    protected int $revision = 1;
    protected int $version = 0;
    protected $category;
    protected $deploymentId;
    protected $resourceName;
    protected $historyLevel;
    protected $startFormHandler;
    protected $startFormDefinition;
    protected $diagramResourceName;
    protected $isGraphicalNotationDefined;
    protected $taskDefinitions = [];
    protected $hasStartFormKey;
    protected $suspensionState;
    protected $tenantId;
    protected $versionTag;
    protected $historyTimeToLive;
    protected bool $isIdentityLinksInitialized = false;
    protected $definitionIdentityLinkEntities = [];
    protected $candidateStarterUserIdExpressions = [];
    protected $candidateStarterGroupIdExpressions = [];
    protected bool $isStartableInTasklist = false;

    // firstVersion is true, when version == 1 or when
    // this definition does not have any previous definitions
    protected bool $firstVersion = false;
    protected $previousProcessDefinitionId;

    public function __construct()
    {
        parent::__construct(null);
        $this->suspensionState = SuspensionState::active()->getStateCode();
    }

    protected function ensureNotSuspended(): void
    {
        if ($this->isSuspended()) {
            //throw LOG.suspendedEntityException("Process Definition", id);
            throw new \Exception("Process Definition");
        }
    }

    protected function newProcessInstance(): PvmExecutionImpl
    {
        $newExecution = ExecutionEntity::createNewExecution();

        if ($this->tenantId !== null) {
            $newExecution->setTenantId($this->tenantId);
        }

        return $newExecution;
    }

    public function createProcessInstance(?string $businessKey = null, ?string $caseInstanceId = null, ?ActivityImpl $initial = null): ExecutionEntity
    {
        if ($initial !== null) {
            $this->ensureNotSuspended();

            $processInstance = $this->createProcessInstanceForInitial($initial);

            // do not reset executions (CAM-2557)!
            // processInstance->setExecutions(new ArrayList<ExecutionEntity>());

            $processInstance->setProcessDefinition($this->processDefinition);

            // Do not initialize variable map (let it happen lazily)

            // reset the process instance in order to have the db-generated process instance id available
            $processInstance->setProcessInstance($processInstance);

            // initialize business key
            if ($businessKey !== null) {
                $processInstance->setBusinessKey($businessKey);
            }

            // initialize case instance id
            /*if ($caseInstanceId !== null) {
                $processInstance->setCaseInstanceId($caseInstanceId);
            }*/

            if ($this->tenantId !== null) {
                $processInstance->setTenantId($this->tenantId);
            }

            return $processInstance;
        } else {
            return parent::createProcessInstance($businessKey, $caseInstanceId, $initial);
        }
    }

    public function addIdentityLink(?string $userId, ?string $groupId): IdentityLinkEntity
    {
        $identityLinkEntity = IdentityLinkEntity::newIdentityLink();
        $this->getIdentityLinks();
        $this->definitionIdentityLinkEntities[] = $identityLinkEntity;
        $identityLinkEntity->setProcessDef($this);
        $identityLinkEntity->setUserId($this->userId);
        $identityLinkEntity->setGroupId($this->groupId);
        $identityLinkEntity->setType(IdentityLinkType::CANDIDATE);
        $identityLinkEntity->setTenantId($this->getTenantId());
        $identityLinkEntity->insert();
        return $identityLinkEntity;
    }

    public function deleteIdentityLink(?string $userId, ?string $groupId): void
    {
        $identityLinks = Context::getCommandContext()
            ->getIdentityLinkManager()
            ->findIdentityLinkByProcessDefinitionUserAndGroup($this->id, $userId, $groupId);

        foreach ($identityLinks as $identityLink) {
            $identityLink->delete();
        }
    }

    public function getIdentityLinks(): array
    {
        if (!$this->isIdentityLinksInitialized) {
            $this->definitionIdentityLinkEntities = Context::getCommandContext()
            ->getIdentityLinkManager()
            ->findIdentityLinksByProcessDefinitionId($this->id);
            $this->isIdentityLinksInitialized = true;
        }

        return $this->definitionIdentityLinkEntities;
    }

    public function __toString()
    {
        return "ProcessDefinitionEntity[" . $this->id . "]";
    }

    /**
     * Updates all modifiable fields from another process definition entity.
     * @param updatingProcessDefinition
     */
    public function updateModifiableFieldsFromEntity(/*ProcessDefinitionEntity*/$updatingProcessDefinition): void
    {
        if ($this->key == $updatingProcessDefinition->key && $this->deploymentId == $updatingProcessDefinition->deploymentId) {
            // TODO: add a guard once the mismatch between revisions in deployment cache and database has been resolved
            $this->revision = $updatingProcessDefinition->revision;
            $this->suspensionState = $updatingProcessDefinition->suspensionState;
            $this->historyTimeToLive = $updatingProcessDefinition->historyTimeToLive;
        } else {
            //LOG.logUpdateUnrelatedProcessDefinitionEntity($this->key, updatingProcessDefinition.key, $this->deploymentId, updatingProcessDefinition.deploymentId);
        }
    }

    // previous process definition //////////////////////////////////////////////

    public function getPreviousDefinition(): ?ProcessDefinitionEntity
    {
        $previousProcessDefinition = null;

        $previousProcessDefinitionId = $this->getPreviousProcessDefinitionId();
        if ($previousProcessDefinitionId !== null) {
            $previousProcessDefinition = $this->loadProcessDefinition($previousProcessDefinitionId);

            if ($previousProcessDefinition === null) {
                $this->resetPreviousProcessDefinitionId();
                $previousProcessDefinitionId = $this->getPreviousProcessDefinitionId();

                if ($previousProcessDefinitionId !== null) {
                    $previousProcessDefinition = $this->loadProcessDefinition($previousProcessDefinitionId);
                }
            }
        }

        return $previousProcessDefinition;
    }

    /**
     * Returns the cached version if exists; does not update the entity from the database in that case
     */
    protected function loadProcessDefinition(?string $processDefinitionId): ProcessDefinitionEntity
    {
        $configuration = Context::getProcessEngineConfiguration();
        $deploymentCache = $configuration->getDeploymentCache();

        $processDefinition = $deploymentCache->findProcessDefinitionFromCache($processDefinitionId);

        if ($processDefinition === null) {
            $commandContext = Context::getCommandContext();
            $processDefinitionManager = $commandContext->getProcessDefinitionManager();
            $processDefinition = $processDefinitionManager->findLatestProcessDefinitionById($processDefinitionId);

            if ($processDefinition !== null) {
                $processDefinition = $deploymentCache->resolveProcessDefinition($processDefinition);
            }
        }

        return $processDefinition;
    }

    public function getPreviousProcessDefinitionId(): ?string
    {
        $this->ensurePreviousProcessDefinitionIdInitialized();
        return $this->previousProcessDefinitionId;
    }

    protected function resetPreviousProcessDefinitionId(): void
    {
        $this->previousProcessDefinitionId = null;
        $this->ensurePreviousProcessDefinitionIdInitialized();
    }

    protected function setPreviousProcessDefinitionId(?string $previousProcessDefinitionId): void
    {
        $this->previousProcessDefinitionId = $previousProcessDefinitionId;
    }

    protected function ensurePreviousProcessDefinitionIdInitialized(): void
    {
        if ($this->previousProcessDefinitionId === null && !$this->firstVersion) {
            $this->previousProcessDefinitionId = Context::getCommandContext()
                ->getProcessDefinitionManager()
                ->findPreviousProcessDefinitionId($this->key, $this->version, $this->tenantId);

            if ($this->previousProcessDefinitionId === null) {
                $this->firstVersion = true;
            }
        }
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["suspensionState"] = $this->suspensionState;
        $persistentState["historyTimeToLive"] = $this->historyTimeToLive;
        return $persistentState;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    public function getDescription(): ?string
    {
        return $this->getProperty(BpmnParse::PROPERTYNAME_DOCUMENTATION);
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(?string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
        $this->firstVersion = ($this->version == 1);
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getResourceName(): ?string
    {
        return $this->resourceName;
    }

    public function setResourceName(?string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    public function getHistoryLevel(): int
    {
        return $this->historyLevel;
    }

    public function setHistoryLevel(int $historyLevel): void
    {
        $this->historyLevel = $historyLevel;
    }

    public function getStartFormHandler(): StartFormHandlerInterface
    {
        return $this->startFormHandler;
    }

    public function setStartFormHandler(StartFormHandlerInterface $startFormHandler): void
    {
        $this->startFormHandler = $startFormHandler;
    }

    public function getStartFormDefinition(): FormDefinition
    {
        return $this->startFormDefinition;
    }

    public function setStartFormDefinition(FormDefinition $startFormDefinition): void
    {
        $this->startFormDefinition = $startFormDefinition;
    }

    public function getTaskDefinitions(): array
    {
        return $this->taskDefinitions;
    }

    public function addTaskDefinition(?string $taskDefinitionKey, TaskDefinition $def): void
    {
        $this->taskDefinitions[$taskDefinitionKey] = $def;
    }

    public function setTaskDefinitions(array $taskDefinitions): void
    {
        $this->taskDefinitions = $taskDefinitions;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getDiagramResourceName(): ?string
    {
        return $this->diagramResourceName;
    }

    public function setDiagramResourceName(?string $diagramResourceName): void
    {
        $this->diagramResourceName = $diagramResourceName;
    }

    public function hasStartFormKey(): bool
    {
        return $this->hasStartFormKey;
    }

    public function getHasStartFormKey(): bool
    {
        return $this->hasStartFormKey;
    }

    public function setStartFormKey(bool $hasStartFormKey): void
    {
        $this->hasStartFormKey = $hasStartFormKey;
    }

    public function setHasStartFormKey(bool $hasStartFormKey): void
    {
        $this->hasStartFormKey = $hasStartFormKey;
    }

    public function isGraphicalNotationDefined(): bool
    {
        return $this->isGraphicalNotationDefined;
    }

    public function setGraphicalNotationDefined(bool $isGraphicalNotationDefined): void
    {
        $this->isGraphicalNotationDefined = $isGraphicalNotationDefined;
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

    public function getSuspensionState(): int
    {
        return $this->suspensionState;
    }

    public function setSuspensionState(int $suspensionState): void
    {
        $this->suspensionState = $suspensionState;
    }

    public function isSuspended(): bool
    {
        return $this->suspensionState == SuspensionState::suspended()->getStateCode();
    }

    public function getCandidateStarterUserIdExpressions(): array
    {
        return $this->candidateStarterUserIdExpressions;
    }

    public function addCandidateStarterUserIdExpression(ExpressionInterface $userId): void
    {
        $this->candidateStarterUserIdExpressions[] = $userId;
    }

    public function getCandidateStarterGroupIdExpressions(): array
    {
        return $this->candidateStarterGroupIdExpressions;
    }

    public function addCandidateStarterGroupIdExpression(ExpressionInterface $groupId): void
    {
        $this->candidateStarterGroupIdExpressions[] = $groupId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getVersionTag(): ?string
    {
        return $this->versionTag;
    }

    public function setVersionTag(?string $versionTag): void
    {
        $this->versionTag = $versionTag;
    }

    public function getHistoryTimeToLive(): ?int
    {
        return $this->historyTimeToLive;
    }

    public function setHistoryTimeToLive(?int $historyTimeToLive): void
    {
        $this->historyTimeToLive = $historyTimeToLive;
    }

    public function isStartableInTasklist(): bool
    {
        return $this->isStartableInTasklist;
    }

    public function setStartableInTasklist(bool $isStartableInTasklist): void
    {
        $this->isStartableInTasklist = $isStartableInTasklist;
    }
}
