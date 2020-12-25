<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Impl\Util\StringUtil;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Builder\ProcessBuilder;
use BpmPlatform\Model\Bpmn\ProcessType;
use BpmPlatform\Model\Bpmn\Instance\{
    ArtifactInterface,
    AuditingInterface,
    CallableElementInterface,
    CorrelationSubscriptionInterface,
    FlowElementInterface,
    LaneSetInterface,
    MonitoringInterface,
    ProcessInterface,
    PropertyInterface,
    ResourceRoleInterface
};

class ProcessImpl extends CallableElementImpl implements ProcessInterface
{
    protected static $processTypeAttribute;
    protected static $isClosedAttribute;
    protected static $isExecutableAttribute;
    protected static $auditingChild;
    protected static $monitoringChild;
    protected static $propertyCollection;
    protected static $laneSetCollection;
    protected static $flowElementCollection;
    protected static $artifactCollection;
    protected static $resourceRoleCollection;
    protected static $correlationSubscriptionCollection;
    protected static $supportsCollection;
    protected static $candidateStarterGroupsAttribute;
    protected static $candidateStarterUsersAttribute;
    protected static $jobPriorityAttribute;
    protected static $taskPriorityAttribute;
    protected static $timeToLiveAttribute;
    protected static $isStartableInTasklistAttribute;
    protected static $versionTagAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $bpmnModelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ProcessInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_PROCESS
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(CallableElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ProcessImpl($instanceContext);
                }
            }
        );

        self::$processTypeAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_PROCESS_TYPE,
            ProcessType::class
        )
        ->defaultValue(ProcessType::NONE)
        ->build();

        self::$isClosedAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_CLOSED)
        ->defaultValue(false)
        ->build();

        self::$isExecutableAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_EXECUTABLE)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$auditingChild = $sequenceBuilder->element(AuditingInterface::class)
        ->build();

        self::$monitoringChild = $sequenceBuilder->element(MonitoringInterface::class)
        ->build();

        self::$propertyCollection = $sequenceBuilder->elementCollection(PropertyInterface::class)
        ->build();

        self::$laneSetCollection = $sequenceBuilder->elementCollection(LaneSetInterface::class)
        ->build();

        self::$flowElementCollection = $sequenceBuilder->elementCollection(FlowElementInterface::class)
        ->build();

        self::$artifactCollection = $sequenceBuilder->elementCollection(ArtifactInterface::class)
        ->build();

        self::$resourceRoleCollection = $sequenceBuilder->elementCollection(ResourceRoleInterface::class)
        ->build();

        self::$correlationSubscriptionCollection = $sequenceBuilder->elementCollection(
            CorrelationSubscriptionInterface::class
        )
        ->build();

        self::$supportsCollection = $sequenceBuilder->elementCollection(Supports::class)
        ->qNameElementReferenceCollection(ProcessInterface::class)
        ->build();

        self::$candidateStarterGroupsAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::ATTRIBUTE_CANDIDATE_STARTER_GROUPS
        )
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$candidateStarterUsersAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::ATTRIBUTE_CANDIDATE_STARTER_USERS
        )
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$jobPriorityAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_JOB_PRIORITY)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$taskPriorityAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_TASK_PRIORITY)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$historyTimeToLiveAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::ATTRIBUTE_HISTORY_TIME_TO_LIVE
        )
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$isStartableInTasklistAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::ATTRIBUTE_IS_STARTABLE_IN_TASKLIST
        )
        ->defaultValue(true)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$versionTagAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_VERSION_TAG)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): ProcessBuilder
    {
        return new ProcessBuilder($this->modelInstance, $this);
    }

    public function getProcessType(): string
    {
        return self::$processTypeAttribute->getValue($this);
    }

    public function setProcessType(string $processType): void
    {
        self::$processTypeAttribute->setValue($this, $processType);
    }

    public function isClosed(): bool
    {
        return self::$isClosedAttribute->getValue($this);
    }

    public function setClosed(bool $closed): void
    {
        self::$isClosedAttribute->setValue($this, $closed);
    }

    public function isExecutable(): bool
    {
        return self::$isExecutableAttribute->getValue($this);
    }

    public function setExecutable(bool $executable): void
    {
        self::$isExecutableAttribute->setValue($this, $executable);
    }

    public function getAuditing(): AuditingInterface
    {
        return self::$auditingChild->getChild($this);
    }

    public function setAuditing(AuditingInterface $auditing): void
    {
        self::$auditingChild->setChild($this, $auditing);
    }

    public function getMonitoring(): MonitoringInterface
    {
        return self::$monitoringChild->getChild($this);
    }

    public function setMonitoring(MonitoringInterface $monitoring): void
    {
        self::$monitoringChild->setChild($this, $monitoring);
    }

    public function getProperties(): array
    {
        return self::$propertyCollection->get($this);
    }

    public function getLaneSets(): array
    {
        return self::$laneSetCollection->get($this);
    }

    public function getFlowElements(): array
    {
        return self::$flowElementCollection->get($this);
    }

    public function getArtifacts(): array
    {
        return self::$artifactCollection->get($this);
    }

    public function getCorrelationSubscriptions(): array
    {
        return self::$correlationSubscriptionCollection->get($this);
    }

    public function getResourceRoles(): array
    {
        return self::$resourceRoleCollection->get($this);
    }

    public function getSupports(): array
    {
        return self::$supportsCollection->getReferenceTargetElements($this);
    }

    public function getCandidateStarterGroups(): string
    {
        return self::$candidateStarterGroupsAttribute->getValue($this);
    }

    public function setCandidateStarterGroups(string $candidateStarterGroups): void
    {
        self::$candidateStarterGroupsAttribute->setValue($this, $candidateStarterGroups);
    }

    public function getCandidateStarterGroupsList(): array
    {
        $groupsString = self::$candidateStarterGroupsAttribute->getValue($this);
        return StringUtil::splitCommaSeparatedList($groupsString);
    }

    public function setCandidateStarterGroupsList(array $candidateStarterGroupsList): void
    {
        $candidateStarterGroups = StringUtil::joinCommaSeparatedList($candidateStarterGroupsList);
        self::$candidateStarterGroupsAttribute->setValue($this, $candidateStarterGroups);
    }

    public function getCandidateStarterUsers(): string
    {
        return self::$candidateStarterUsersAttribute->getValue($this);
    }

    public function setCandidateStarterUsers(string $candidateStarterUsers): void
    {
        self::$candidateStarterUsersAttribute->setValue($this, $candidateStarterUsers);
    }

    public function getCandidateStarterUsersList(): array
    {
        $candidateStarterUsers = self::$candidateStarterUsersAttribute->getValue($this);
        return StringUtil::splitCommaSeparatedList($candidateStarterUsers);
    }

    public function setCandidateStarterUsersList(array $candidateStarterUsersList): void
    {
        $candidateStarterUsers = StringUtil::joinCommaSeparatedList(self::$candidateStarterUsersList);
        self::$candidateStarterUsersAttribute->setValue($this, $candidateStarterUsers);
    }

    public function getJobPriority(): string
    {
        return self::$jobPriorityAttribute->getValue($this);
    }

    public function setJobPriority(string $jobPriority): void
    {
        self::$jobPriorityAttribute->setValue($this, $jobPriority);
    }

    public function getTaskPriority(): string
    {
        return self::$taskPriorityAttribute->getValue($this);
    }

    public function setTaskPriority(string $taskPriority): void
    {
        self::$taskPriorityAttribute->setValue($this, $taskPriority);
    }

    public function getHistoryTimeToLive(): ?int
    {
        $ttl = $this->getHistoryTimeToLiveString();
        if (!empty($ttl)) {
            return intval($ttl);
        }
        return null;
    }

    public function setHistoryTimeToLive(int $historyTimeToLive): void
    {
        $this->setHistoryTimeToLiveString(strval($historyTimeToLive));
    }

    public function getHistoryTimeToLiveString(): ?string
    {
        return self::$historyTimeToLiveAttribute->getValue($this);
    }

    public function setHistoryTimeToLiveString(string $historyTimeToLive): void
    {
        self::$historyTimeToLiveAttribute->setValue($this, $historyTimeToLive);
    }

    public function isStartableInTasklist(): bool
    {
        return self::$isStartableInTasklistAttribute->getValue($this);
    }

    public function setStartableInTasklist(bool $isStartableInTasklist): void
    {
        self::$isStartableInTasklistAttribute->setValue($this, $isStartableInTasklist);
    }

    public function getVersionTag(): string
    {
        return self::$versionTagAttribute->getValue($this);
    }

    public function setVersionTag(string $versionTag): void
    {
        self::$versionTagAttribute->setValue($this, $versionTag);
    }
}
