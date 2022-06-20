<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\{
    BadUserRequestException,
    ProcessEngineException
};
use Jabe\Engine\History\{
    HistoricProcessInstanceInterface,
    HistoricProcessInstanceQueryInterface
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\{
    CompareUtil,
    EnsureUtil,
    ImmutablePair
};
use Jabe\Engine\Impl\Variable\Serializer\VariableSerializersInterface;

class HistoricProcessInstanceQueryImpl extends AbstractVariableQueryImpl implements HistoricProcessInstanceQueryInterface
{
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionName;
    protected $processDefinitionNameLike;
    protected $businessKey;
    protected $businessKeyIn = [];
    protected $businessKeyLike;
    protected $finished = false;
    protected $unfinished = false;
    protected $withIncidents = false;
    protected $withRootIncidents = false;
    protected $incidentType;
    protected $incidentStatus;
    protected $incidentMessage;
    protected $incidentMessageLike;
    protected $startedBy;
    protected $isRootProcessInstances;
    protected $superProcessInstanceId;
    protected $subProcessInstanceId;
    //protected $superCaseInstanceId;
    //protected $subCaseInstanceId;
    protected $processKeyNotIn = [];
    protected $startedBefore;
    protected $startedAfter;
    protected $finishedBefore;
    protected $finishedAfter;
    protected $executedActivityAfter;
    protected $executedActivityBefore;
    protected $executedJobAfter;
    protected $executedJobBefore;
    protected $processDefinitionKey;
    protected $processDefinitionKeys = [];
    protected $processInstanceIds = [];
    protected $tenantIds = [];
    protected $isTenantIdSet;
    protected $executedActivityIds = [];
    protected $activeActivityIds = [];
    protected $state;
    protected $caseInstanceId;
    protected $queries = [];
    protected $isOrQueryActive = false;
    protected $queryVariableNameToValuesMap = [];

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
        $this->queries[] = $this;
    }

    public function processInstanceId(string $processInstanceId): HistoricProcessInstanceQueryImpl
    {
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processInstanceIds(array $processInstanceIds): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotEmpty("Set of process instance ids", "processInstanceIds", $processInstanceIds);
        $this->processInstanceIds = $processInstanceIds;
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): HistoricProcessInstanceQueryImpl
    {
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(string $processDefinitionKey): HistoricProcessInstanceQueryInterface
    {
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKeys", "processDefinitionKeys", $processDefinitionKeys);
        $this->processDefinitionKeys = $processDefinitionKeys;
        return $this;
    }

    public function processDefinitionName(string $processDefinitionName): HistoricProcessInstanceQueryInterface
    {
        $this->processDefinitionName = $processDefinitionName;
        return $this;
    }

    public function processDefinitionNameLike(string $nameLike): HistoricProcessInstanceQueryInterface
    {
        $this->processDefinitionNameLike = $nameLike;
        return $this;
    }

    public function processInstanceBusinessKey(string $businessKey): HistoricProcessInstanceQueryInterface
    {
        $this->businessKey = $businessKey;
        return $this;
    }

    public function processInstanceBusinessKeyIn(array $businessKeyIn): HistoricProcessInstanceQueryInterface
    {
        $this->businessKeyIn = $businessKeyIn;
        return $this;
    }

    public function processInstanceBusinessKeyLike(string $businessKeyLike): HistoricProcessInstanceQueryInterface
    {
        $this->businessKeyLike = $businessKeyLike;
        return $this;
    }

    public function finished(): HistoricProcessInstanceQueryInterface
    {
        $this->finished = true;
        return $this;
    }

    public function unfinished(): HistoricProcessInstanceQueryInterface
    {
        $this->unfinished = true;
        return $this;
    }

    public function withIncidents(): HistoricProcessInstanceQueryInterface
    {
        $this->withIncidents = true;
        return $this;
    }

    public function withRootIncidents(): HistoricProcessInstanceQueryInterface
    {
        $this->withRootIncidents = true;
        return $this;
    }

    public function incidentType(string $incidentType): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("incident type", "incidentType", $incidentType);
        $this->incidentType = $incidentType;
        return $this;
    }

    public function incidentStatus(string $status): HistoricProcessInstanceQueryInterface
    {
        $this->incidentStatus = $status;
        return $this;
    }

    public function incidentMessage(string $incidentMessage): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("incidentMessage", "incidentMessage", $incidentMessage);
        $this->incidentMessage = $incidentMessage;
        return $this;
    }

    public function incidentMessageLike(string $incidentMessageLike): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("incidentMessageLike", "incidentMessageLike", $incidentMessageLike);
        $this->incidentMessageLike = $incidentMessageLike;
        return $this;
    }

    public function startedBy(string $userId): HistoricProcessInstanceQueryInterface
    {
        $this->startedBy = $userId;
        return $this;
    }

    public function processDefinitionKeyNotIn(array $processDefinitionKeys): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotContainsNull("processDefinitionKeys", "processDefinitionKeys", $processDefinitionKeys);
        EnsureUtil::ensureNotContainsEmptyString("processDefinitionKeys", "processDefinitionKeys", $processDefinitionKeys);
        $this->processKeyNotIn = $processDefinitionKeys;
        return $this;
    }

    public function startedAfter(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->startedAfter = $date;
        return $this;
    }

    public function startedBefore(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->startedBefore = $date;
        return $this;
    }

    public function finishedAfter(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->finishedAfter = $date;
        $this->finished = true;
        return $this;
    }

    public function finishedBefore(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->finishedBefore = $date;
        $this->finished = true;
        return $this;
    }

    public function rootProcessInstances(): HistoricProcessInstanceQueryInterface
    {
        if ($this->superProcessInstanceId != null) {
            throw new BadUserRequestException("Invalid query usage: cannot set both rootProcessInstances and superProcessInstanceId");
        }
        /*if (superCaseInstanceId != null) {
            throw new BadUserRequestException("Invalid query usage: cannot set both rootProcessInstances and superCaseInstanceId");
        }*/
        $this->isRootProcessInstances = true;
        return $this;
    }

    public function superProcessInstanceId(string $superProcessInstanceId): HistoricProcessInstanceQueryInterface
    {
        if ($this->isRootProcessInstances) {
            throw new BadUserRequestException("Invalid query usage: cannot set both rootProcessInstances and superProcessInstanceId");
        }
        $this->superProcessInstanceId = $superProcessInstanceId;
        return $this;
    }

    public function subProcessInstanceId(string $subProcessInstanceId): HistoricProcessInstanceQueryInterface
    {
        $this->subProcessInstanceId = $subProcessInstanceId;
        return $this;
    }

    /*public function superCaseInstanceId(string $superCaseInstanceId): HistoricProcessInstanceQueryInterface
    {
        if ($this->isRootProcessInstances) {
            throw new BadUserRequestException("Invalid query usage: cannot set both rootProcessInstances and superCaseInstanceId");
        }
        $this->superCaseInstanceId = superCaseInstanceId;
        return $this;
    }

    public HistoricProcessInstanceQueryInterface subCaseInstanceId(string $subCaseInstanceId) {
        $this->subCaseInstanceId = subCaseInstanceId;
        return $this;
    }

    public HistoricProcessInstanceQueryInterface caseInstanceId(string $caseInstanceId) {
        $this->caseInstanceId = caseInstanceId;
        return $this;
    }*/

    public function tenantIdIn(array $tenantIds): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricProcessInstanceQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
            || ($this->finished && $this->unfinished)
            || CompareUtil::areNotInAscendingOrder($this->startedAfter, $this->startedBefore)
            || CompareUtil::areNotInAscendingOrder($this->finishedAfter, $this->finishedBefore)
            || CompareUtil::elementIsContainedInList($this->processDefinitionKey, $this->processKeyNotIn)
            || CompareUtil::elementIsNotContainedInList($this->processInstanceId, $this->processInstanceIds);
    }

    public function orderByProcessInstanceBusinessKey(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceBusinessKey() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::businessKey());
    }

    public function orderByProcessInstanceDuration(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceDuration() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::duration());
    }

    public function orderByProcessInstanceStartTime(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceStartTime() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::startTime());
    }

    public function orderByProcessInstanceEndTime(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceEndTime() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::endTime());
    }

    public function orderByProcessDefinitionId(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessDefinitionId() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::processDefinitionId());
    }

    public function orderByProcessDefinitionKey(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessDefinitionKey() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::processDefinitionKey());
    }

    public function orderByProcessDefinitionName(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessDefinitionName() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::processDefinitionName());
    }

    public function orderByProcessDefinitionVersion(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessDefinitionVersion() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::processDefinitionVersion());
    }

    public function orderByProcessInstanceId(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceId() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::processInstanceId());
    }

    public function orderByTenantId(): HistoricProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTenantId() within 'or' query");
        }
        return $this->orderBy(HistoricProcessInstanceQueryProperty::tenantId());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        EnsureUtil::ensureVariablesInitialized();
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findHistoricProcessInstanceCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        EnsureUtil::ensureVariablesInitialized();
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findHistoricProcessInstancesByQueryCriteria($this, $page);
    }

    public function executeIdsList(CommandContext $commandContext): array
    {
        $this->checkQueryOk();
        EnsureUtil::ensureVariablesInitialized();
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findHistoricProcessInstanceIds($this);
    }

    public function executeDeploymentIdMappingsList(CommandContext $commandContext): array
    {
        $this->checkQueryOk();
        EnsureUtil::ensureVariablesInitialized();
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findDeploymentIdMappingsByQueryCriteria($this);
    }

    public function getQueryVariableValues(): array
    {
        return array_values($this->queryVariableNameToValuesMap);
    }

    public function getQueryVariableNameToValuesMap(): array
    {
        return $this->queryVariableNameToValuesMap;
    }

    protected function ensureVariablesInitialized(): void
    {
        parent::ensureVariablesInitialized();

        if (!empty($this->queries)) {
            $processEngineConfiguration = Context::getProcessEngineConfiguration();
            $variableSerializers = $processEngineConfiguration->getVariableSerializers();
            $dbType = $processEngineConfiguration->getDatabaseType();

            foreach ($this->queries as $orQuery) {
                foreach ($orQuery->getQueryVariableValues() as $var) {
                    $var->initialize($variableSerializers, $dbType);
                }
            }
        }
    }

    protected function addVariable(string $name, $value, string $operator, bool $processInstanceScope): void
    {
        $queryVariableValue = $this->createQueryVariableValue($name, $value, $operator, $processInstanceScope);
        if (!array_key_exists($name, $this->queryVariableNameToValuesMap)) {
            $this->queryVariableNameToValuesMap[$name] = [$queryVariableValue];
        } else {
            $this->queryVariableNameToValuesMap[$name][] = $queryVariableValue;
        }
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function addOrQuery(HistoricProcessInstanceQueryImpl $orQuery): void
    {
        $orQuery->isOrQueryActive = true;
        $this->queries[] = $orQuery;
    }

    public function setOrQueryActive(): void
    {
        $this->isOrQueryActive = true;
    }

    public function isOrQueryActive(): bool
    {
        return $this->isOrQueryActive;
    }

    public function getActiveActivityIds(): array
    {
        return $this->activeActivityIds;
    }

    public function getBusinessKey(): string
    {
        return $this->businessKey;
    }

    public function getBusinessKeyIn(): array
    {
        return $this->businessKeyIn;
    }

    public function getBusinessKeyLike(): string
    {
        return $this->businessKeyLike;
    }

    public function getExecutedActivityIds(): array
    {
        return $this->executedActivityIds;
    }

    public function getExecutedActivityAfter(): string
    {
        return $this->executedActivityAfter;
    }

    public function getExecutedActivityBefore(): string
    {
        return $this->executedActivityBefore;
    }

    public function getExecutedJobAfter(): string
    {
        return $this->executedJobAfter;
    }

    public function getExecutedJobBefore(): string
    {
        return $this->executedJobBefore;
    }

    public function isOpen(): bool
    {
        return $this->unfinished;
    }

    public function isUnfinished(): bool
    {
        return $this->unfinished;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionKeys(): array
    {
        return $this->processDefinitionKeys;
    }

    public function getProcessDefinitionIdLike(): string
    {
        return $this->processDefinitionKey . ":%:%";
    }

    public function getProcessDefinitionName(): string
    {
        return $this->processDefinitionName;
    }

    public function getProcessDefinitionNameLike(): string
    {
        return $this->processDefinitionNameLike;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getStartedBy(): string
    {
        return $this->startedBy;
    }

    public function getSuperProcessInstanceId(): string
    {
        return $this->superProcessInstanceId;
    }

    public function setSuperProcessInstanceId(string $superProcessInstanceId): void
    {
        $this->superProcessInstanceId = $superProcessInstanceId;
    }

    public function getProcessKeyNotIn(): array
    {
        return $this->processKeyNotIn;
    }

    public function getStartedAfter(): string
    {
        return $this->startedAfter;
    }

    public function getStartedBefore(): string
    {
        return $this->startedBefore;
    }

    public function getFinishedAfter(): string
    {
        return $this->finishedAfter;
    }

    public function getFinishedBefore(): string
    {
        return $this->finishedBefore;
    }

    /*public String getCaseInstanceId() {
        return caseInstanceId;
    }*/

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

    public function getIncidentStatus(): string
    {
        return $this->incidentStatus;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getFinishDateBy(): string
    {
        return $this->finishDateBy;
    }

    public function getStartDateBy(): string
    {
        return $this->startDateBy;
    }

    public function getStartDateOn(): string
    {
        return $this->startDateOn;
    }

    public function getStartDateOnBegin(): string
    {
        return $this->startDateOnBegin;
    }

    public function getStartDateOnEnd(): string
    {
        return $this->startDateOnEnd;
    }

    public function getFinishDateOn(): string
    {
        return $this->finishDateOn;
    }

    public function getFinishDateOnBegin(): string
    {
        return $this->finishDateOnBegin;
    }

    public function getFinishDateOnEnd(): string
    {
        return $this->finishDateOnEnd;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function getIsTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function isWithIncidents(): bool
    {
        return $this->withIncidents;
    }

    public function isWithRootIncidents(): bool
    {
        return $this->withRootIncidents;
    }

    protected $startDateBy;
    protected $startDateOn;
    protected $finishDateBy;
    protected $finishDateOn;
    protected $startDateOnBegin;
    protected $startDateOnEnd;
    protected $finishDateOnBegin;
    protected $finishDateOnEnd;

    public function startDateBy(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->startDateBy = $this->calculateMidnight($date);
        return $this;
    }

    public function startDateOn(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->startDateOn = $date;
        $this->startDateOnBegin = $this->calculateMidnight($date);
        $this->startDateOnEnd = $this->calculateBeforeMidnight($date);
        return $this;
    }

    public function finishDateBy(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->finishDateBy = $this->calculateBeforeMidnight($date);
        return $this;
    }

    public function finishDateOn(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->finishDateOn = $date;
        $this->finishDateOnBegin = $this->calculateMidnight($date);
        $this->finishDateOnEnd = $this->calculateBeforeMidnight($date);
        return $this;
    }

    private function calculateBeforeMidnight(string $date): string
    {
        return (new \DateTime())->setTimestamp(strtotime("+1 day -1 second", strtotime($date)))->format('c');
    }

    private function calculateMidnight(string $date): string
    {
        $date = new \DateTime($date);
        $date->setTime(0, 0, 0, 0);
        return $date->format('c');
    }

    public function isRootProcessInstances(): bool
    {
        return $this->isRootProcessInstances;
    }

    public function getSubProcessInstanceId(): string
    {
        return $this->subProcessInstanceId;
    }

    /*public String getSuperCaseInstanceId() {
        return superCaseInstanceId;
    }

    public String getSubCaseInstanceId() {
        return subCaseInstanceId;
    }*/

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    public function executedActivityAfter(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->executedActivityAfter = $date;
        return $this;
    }

    public function executedActivityBefore(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->executedActivityBefore = $date;
        return $this;
    }

    public function executedJobAfter(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->executedJobAfter = $date;
        return $this;
    }

    public function executedJobBefore(string $date): HistoricProcessInstanceQueryInterface
    {
        $this->executedJobBefore = $date;
        return $this;
    }

    public function executedActivityIdIn(array $ids): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("activity ids", "ids", $ids);
        EnsureUtil::ensureNotContainsNull("activity ids", "ids", $ids);
        $this->executedActivityIds = $ids;
        return $this;
    }

    public function activeActivityIdIn(array $ids): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("activity ids", "ids", $ids);
        EnsureUtil::ensureNotContainsNull("activity ids", "ids", $ids);
        $this->activeActivityIds = $ids;
        return $this;
    }

    public function active(): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNull("Already querying for historic process instance with another state", "state", $this->state);
        $state = HistoricProcessInstanceInterface::STATE_ACTIVE;
        return $this;
    }

    public function suspended(): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNull("Already querying for historic process instance with another state", "state", $this->state);
        $this->state = HistoricProcessInstanceInterface::STATE_SUSPENDED;
        return $this;
    }

    public function completed(): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNull("Already querying for historic process instance with another state", "state", $this->state);
        $this->state = HistoricProcessInstanceInterface::STATE_COMPLETED;
        return $this;
    }

    public function externallyTerminated(): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNull("Already querying for historic process instance with another state", "state", $this->state);
        $this->state = HistoricProcessInstanceInterface::STATE_EXTERNALLY_TERMINATED;
        return $this;
    }

    public function internallyTerminated(): HistoricProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNull("Already querying for historic process instance with another state", "state", $this->state);
        $this->state = HistoricProcessInstanceInterface::STATE_INTERNALLY_TERMINATED;
        return $this;
    }

    public function or(): HistoricProcessInstanceQueryInterface
    {
        if ($this != $this->queries[0]) {
            throw new ProcessEngineException("Invalid query usage: cannot set or() within 'or' query");
        }
        $orQuery = new HistoricProcessInstanceQueryImpl();
        $orQuery->isOrQueryActive = true;
        $orQuery->queries = $queries;
        $this->queries[] = $orQuery;
        return $orQuery;
    }

    public function endOr(): HistoricProcessInstanceQueryInterface
    {
        if (!empty($this->queries) && $this != $this->queries[count($this->queries) - 1]) {
            throw new ProcessEngineException("Invalid query usage: cannot set endOr() before or()");
        }
        return $this->queries[0];
    }
}
