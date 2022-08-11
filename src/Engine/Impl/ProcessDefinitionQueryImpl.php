<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Identity\GroupInterface;
use Jabe\Engine\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    CompositePermissionCheck,
    PermissionCheck
};
use Jabe\Engine\Impl\Event\EventType;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    SuspensionState
};
use Jabe\Engine\Impl\Util\{
    CompareUtil,
    EnsureUtil
};
use Jabe\Engine\Repository\{
    ProcessDefinitionInterface,
    ProcessDefinitionQueryInterface
};
use Bpmn\Instance\DocumentationInterface;

class ProcessDefinitionQueryImpl extends AbstractQuery implements ProcessDefinitionQueryInterface
{
    protected $id;
    protected $ids = [];
    protected $category;
    protected $categoryLike;
    protected $name;
    protected $nameLike;
    protected $deploymentId;
    protected $deployedAfter;
    protected $deployedAt;
    protected $key;
    protected $keys = [];
    protected $keyLike;
    protected $resourceName;
    protected $resourceNameLike;
    protected $version;
    protected $latest = false;
    protected $suspensionState;
    protected $authorizationUserId;
    protected $cachedCandidateGroups = [];
    protected $procDefId;
    protected $incidentType;
    protected $incidentId;
    protected $incidentMessage;
    protected $incidentMessageLike;

    protected $eventSubscriptionName;
    protected $eventSubscriptionType;

    protected $isTenantIdSet = false;
    protected $tenantIds = [];
    protected $includeDefinitionsWithoutTenantId = false;

    protected $isVersionTagSet = false;
    protected $versionTag;
    protected $versionTagLike;

    protected $isStartableInTasklist = false;
    protected $isNotStartableInTasklist = false;
    protected $startablePermissionCheck = false;
    // for internal use
    protected $processDefinitionCreatePermissionChecks = [];
    private $shouldJoinDeploymentTable = false;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function processDefinitionId(string $processDefinitionId): ProcessDefinitionQueryImpl
    {
        $this->id = $processDefinitionId;
        return $this;
    }

    public function processDefinitionIdIn(array $ids): ProcessDefinitionQueryImpl
    {
        $this->ids = $ids;
        return $this;
    }

    public function processDefinitionCategory(string $category): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("category", "category", $category);
        $this->category = $category;
        return $this;
    }

    public function processDefinitionCategoryLike(string $categoryLike): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("categoryLike", "categoryLike", $categoryLike);
        $this->categoryLike = $categoryLike;
        return $this;
    }

    public function processDefinitionName(string $name): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("name", "name", $name);
        $this->name = $name;
        return $this;
    }

    public function processDefinitionNameLike(string $nameLike): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("nameLike", "nameLike", $nameLike);
        $this->nameLike = $nameLike;
        return $this;
    }

    public function deploymentId(string $deploymentId): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("deploymentId", "deploymentId", $deploymentId);
        $this->deploymentId = $deploymentId;
        return $this;
    }

    public function deployedAfter(string $deployedAfter): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("deployedAfter", "deployedAfter", $deployedAfter);
        $this->shouldJoinDeploymentTable = true;
        $this->deployedAfter = $deployedAfter;
        return $this;
    }

    public function deployedAt(string $deployedAt): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("deployedAt", "deployedAt", $deployedAt);
        $this->shouldJoinDeploymentTable = true;
        $this->deployedAt = $deployedAt;
        return $this;
    }

    public function processDefinitionKey(string $key): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("key", "key", $key);
        $this->key = $key;
        return $this;
    }

    public function processDefinitionKeysIn(array $keys): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("keys", "keys", $keys);
        $this->keys = $keys;
        return $this;
    }

    public function processDefinitionKeyLike(string $keyLike): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("keyLike", "keyLike", $keyLike);
        $this->keyLike = $keyLike;
        return $this;
    }

    public function processDefinitionResourceName(string $resourceName): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("resourceName", "resourceName", $resourceName);
        $this->resourceName = $resourceName;
        return $this;
    }

    public function processDefinitionResourceNameLike(string $resourceNameLike): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("resourceNameLike", "resourceNameLike", $resourceNameLike);
        $this->resourceNameLike = $resourceNameLike;
        return $this;
    }

    public function processDefinitionVersion(int $version): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("version", "version", $version);
        EnsureUtil::ensurePositive("The process definition version must be positive", "version", intval($version));
        $this->version = $version;
        return $this;
    }

    public function latestVersion(): ProcessDefinitionQueryImpl
    {
        $this->latest = true;
        return $this;
    }

    public function active(): ProcessDefinitionQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function suspended(): ProcessDefinitionQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function messageEventSubscription(string $messageName): ProcessDefinitionQueryInterface
    {
        return $this->eventSubscription(EventType::message(), $messageName);
    }

    public function messageEventSubscriptionName(string $messageName): ProcessDefinitionQueryInterface
    {
        return $this->eventSubscription(EventType::message(), $messageName);
    }

    public function processDefinitionStarter(string $procDefId): ProcessDefinitionQueryInterface
    {
        $this->procDefId = $procDefId;
        return $this;
    }

    public function eventSubscription(EventType $eventType, string $eventName): ProcessDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("event type", "eventType", $eventType);
        EnsureUtil::ensureNotNull("event name", "eventName", $eventName);
        $this->eventSubscriptionType = $eventType->name();
        $this->eventSubscriptionName = $eventName;
        return $this;
    }

    public function incidentType(string $incidentType): ProcessDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident type", "incidentType", $incidentType);
        $this->incidentType = $incidentType;
        return $this;
    }

    public function incidentId(string $incidentId): ProcessDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident id", "incidentId", $incidentId);
        $this->incidentId = $incidentId;
        return $this;
    }

    public function incidentMessage(string $incidentMessage): ProcessDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident message", "incidentMessage", $incidentMessage);
        $this->incidentMessage = $incidentMessage;
        return $this;
    }

    public function incidentMessageLike(string $incidentMessageLike): ProcessDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident messageLike", "incidentMessageLike", $incidentMessageLike);
        $this->incidentMessageLike = $incidentMessageLike;
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions() || CompareUtil::elementIsNotContainedInArray($this->id, $this->ids);
    }

    public function tenantIdIn(array $tenantIds): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): ProcessDefinitionQueryInterface
    {
        $this->isTenantIdSet = true;
        $this->tenantIds = null;
        return $this;
    }

    public function includeProcessDefinitionsWithoutTenantId(): ProcessDefinitionQueryInterface
    {
        $this->includeDefinitionsWithoutTenantId  = true;
        return $this;
    }

    public function versionTag(string $versionTag): ProcessDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("versionTag", "versionTag", $versionTag);
        $this->versionTag = $versionTag;
        $this->isVersionTagSet = true;

        return $this;
    }

    public function versionTagLike(string $versionTagLike): ProcessDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("versionTagLike", "versionTagLike", $versionTagLike);
        $this->versionTagLike = $versionTagLike;
        return $this;
    }

    public function withoutVersionTag(): ProcessDefinitionQueryInterface
    {
        $this->isVersionTagSet = true;
        $this->versionTag = null;
        return $this;
    }

    public function startableInTasklist(): ProcessDefinitionQueryInterface
    {
        $this->isStartableInTasklist = true;
        return $this;
    }

    public function notStartableInTasklist(): ProcessDefinitionQueryInterface
    {
        $this->isNotStartableInTasklist = true;
        return $this;
    }

    public function startablePermissionCheck(): ProcessDefinitionQueryInterface
    {
        $this->startablePermissionCheck = true;
        return $this;
    }

    //sorting ////////////////////////////////////////////

    public function orderByDeploymentId(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::deploymentId());
    }

    public function orderByDeploymentTime(): ProcessDefinitionQueryInterface
    {
        $this->shouldJoinDeploymentTable = true;
        return $this->orderBy(new QueryOrderingProperty(QueryOrderingProperty::relationDeployment(), ProcessDefinitionQueryProperty::deployTime()));
    }

    public function orderByProcessDefinitionKey(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::processDefinitionKey());
    }

    public function orderByProcessDefinitionCategory(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::processDefinitionCategory());
    }

    public function orderByProcessDefinitionId(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::processDefinitionId());
    }

    public function orderByProcessDefinitionVersion(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::processDefinitionVersion());
    }

    public function orderByProcessDefinitionName(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::processDefinitionName());
    }

    public function orderByTenantId(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::tenantId());
    }

    public function orderByVersionTag(): ProcessDefinitionQueryInterface
    {
        return $this->orderBy(ProcessDefinitionQueryProperty::versionTag());
    }

    //results ////////////////////////////////////////////

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        // fetch candidate groups
        $this->getCandidateGroups();
        return $commandContext
        ->getProcessDefinitionManager()
        ->findProcessDefinitionCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        // fetch candidate groups
        $this->getCandidateGroups();
        $list = $commandContext
        ->getProcessDefinitionManager()
        ->findProcessDefinitionsByQueryCriteria($this, $page);

        $shouldQueryAddBpmnModelInstancesToCache =
            $commandContext->getProcessEngineConfiguration()->getEnableFetchProcessDefinitionDescription();
        if ($this->shouldQueryAddBpmnModelInstancesToCache) {
            $this->addProcessDefinitionToCacheAndRetrieveDocumentation($list);
        }

        return $list;
    }

    protected function addProcessDefinitionToCacheAndRetrieveDocumentation(array $list): void
    {
        foreach ($this->list as $processDefinition) {
            $bpmnModelInstance = Context::getProcessEngineConfiguration()
                ->getDeploymentCache()
                ->findBpmnModelInstanceForProcessDefinition($processDefinition);

            $processElement = $bpmnModelInstance->getModelElementById($processDefinition->getKey());
            if ($processElement !== null) {
                $documentations = $processElement->getChildElementsByType(DocumentationInterface::class);
                $docStrings = [];
                foreach ($documentations as $documentation) {
                    $docStrings[] = $documentation->getTextContent();
                }

                $processDefinitionEntity = $processDefinition;
                $processDefinitionEntity->setProperty(BpmnParse::PROPERTYNAME_DOCUMENTATION, BpmnParse::parseDocumentation($docStrings));
            }
        }
    }

    public function checkQueryOk(): void
    {
        parent::checkQueryOk();

        if ($this->latest && ( ($this->id !== null) || ($this->version !== null) || ($this->deploymentId !== null))) {
            throw new ProcessEngineException("Calling latest() can only be used in combination with key(String) and keyLike(String) or name(String) and nameLike(String)");
        }
    }

    //getters ////////////////////////////////////////////

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function getDeployedAfter(): string
    {
        return $this->deployedAfter;
    }

    public function getDeployedAt(): string
    {
        return $this->deployedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameLike(): string
    {
        return $this->nameLike;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getKeyLike(): string
    {
        return $this->keyLike;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function isLatest(): bool
    {
        return $this->latest;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCategoryLike(): string
    {
        return $this->categoryLike;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function getResourceNameLike(): string
    {
        return $this->resourceNameLike;
    }

    public function getSuspensionState(): SuspensionState
    {
        return $this->suspensionState;
    }

    public function setSuspensionState(SuspensionState $suspensionState): void
    {
        $this->suspensionState = $suspensionState;
    }

    public function getIncidentId(): string
    {
        return $this->incidentId;
    }

    public function getIncidentType(): string
    {
        return $this->incidentType;
    }

    public function getIncidentMessage(): string
    {
        return $this->incidentMessage;
    }

    public function getIncidentMessageLike(): string
    {
        return $this->incidentMessageLike;
    }

    public function getVersionTag(): string
    {
        return $this->versionTag;
    }

    public function isStartableInTasklist(): bool
    {
        return $this->isStartableInTasklist;
    }

    public function isNotStartableInTasklist(): bool
    {
        return $this->isNotStartableInTasklist;
    }

    public function isStartablePermissionCheck(): bool
    {
        return $this->startablePermissionCheck;
    }

    public function setProcessDefinitionCreatePermissionChecks(array $processDefinitionCreatePermissionChecks): void
    {
        $this->processDefinitionCreatePermissionChecks = $processDefinitionCreatePermissionChecks;
    }

    public function getProcessDefinitionCreatePermissionChecks(): array
    {
        return $this->processDefinitionCreatePermissionChecks;
    }

    public function isShouldJoinDeploymentTable(): bool
    {
        return $this->shouldJoinDeploymentTable;
    }

    public function addProcessDefinitionCreatePermissionCheck(CompositePermissionCheck $processDefinitionCreatePermissionCheck): void
    {
        $this->processDefinitionCreatePermissionChecks = array_merge($this->processDefinitionCreatePermissionChecks, $processDefinitionCreatePermissionCheck->getAllPermissionChecks());
    }

    public function getCandidateGroups(): array
    {
        if (!empty($this->cachedCandidateGroups)) {
            return $this->cachedCandidateGroups;
        }

        if ($this->authorizationUserId !== null) {
            $groups = Context::getCommandContext()
                ->getReadOnlyIdentityProvider()
                ->createGroupQuery()
                ->groupMember($this->authorizationUserId)
                ->list();
            $this->cachedCandidateGroups = array_map(function ($group) {
                return $group->getId();
            }, $groups);
        }

        return $this->cachedCandidateGroups;
    }

    public function startableByUser(string $userId): ProcessDefinitionQueryImpl
    {
        EnsureUtil::ensureNotNull("userId", "userId", $userId);
        $this->authorizationUserId = $userId;
        return $this;
    }
}
