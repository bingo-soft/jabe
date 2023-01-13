<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Util\{
    EnsureUtil//,
    //ImmutablePair
};
use Jabe\Runtime\{
    ProcessInstanceInterface,
    ProcessInstanceQueryInterface
};

class ProcessInstanceQueryImpl extends AbstractVariableQueryImpl implements ProcessInstanceQueryInterface, \Serializable
{
    protected $processInstanceId;
    protected $businessKey;
    protected $businessKeyLike;
    protected $processDefinitionId;
    protected $processInstanceIds = [];
    protected $processDefinitionKey;
    protected $processDefinitionKeys = [];
    protected $processDefinitionKeyNotIn = [];
    protected $deploymentId;
    protected $superProcessInstanceId;
    protected $subProcessInstanceId;
    protected $suspensionState;
    protected $withIncident;
    protected $incidentType;
    protected $incidentId;
    protected $incidentMessage;
    protected $incidentMessageLike;
    /*protected $caseInstanceId;
    protected $superCaseInstanceId;
    protected $subCaseInstanceId;*/
    protected $activityIds = [];
    protected bool $isRootProcessInstances = false;
    protected bool $isLeafProcessInstances = false;

    protected bool $isTenantIdSet = false;
    protected $tenantIds = [];
    protected bool $isProcessDefinitionWithoutTenantId = false;

    // or query /////////////////////////////
    protected $queries = [];//new ArrayList<>(Arrays.asList($this));
    protected bool $isOrQueryActive = false;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
        $this->queries[] = $this;
    }

    public function serialize()
    {
        $queries = [];
        foreach ($queries as $query) {
            $queries[] = serialize($query);
        }
        return json_encode([
            'processInstanceId' => $this->processInstanceId,
            'businessKey' => $this->businessKey,
            'businessKeyLike' => $this->businessKeyLike,
            'processDefinitionId' => $this->processDefinitionId,
            'processInstanceIds' => $this->processInstanceIds,
            'processDefinitionKey' => $this->processDefinitionKey,
            'processDefinitionKeys' => $this->processDefinitionKeys,
            'processDefinitionKeyNotIn' => $this->processDefinitionKeyNotIn,
            'deploymentId' => $this->deploymentId,
            'superProcessInstanceId' => $this->superProcessInstanceId,
            'subProcessInstanceId' => $this->subProcessInstanceId,
            'suspensionState' => serialize($this->suspensionState),
            'withIncident' => $this->withIncident,
            'incidentType' => $this->incidentType,
            'incidentId' => $this->incidentId,
            'incidentMessage' => $this->incidentMessage,
            'incidentMessageLike' => $this->incidentMessageLike,
            'activityIds' => $this->activityIds,
            'isRootProcessInstances' => $this->isRootProcessInstances,
            'isLeafProcessInstances' => $this->isLeafProcessInstances,
            'isTenantIdSet' => $this->isTenantIdSet,
            'tenantIds' => $this->tenantIds,
            'isProcessDefinitionWithoutTenantId' => $this->isProcessDefinitionWithoutTenantId,
            'queries' => $queries,
            'isOrQueryActive' => $this->isOrQueryActive,
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $queries = [];
        foreach ($data->queries as $query) {
            $queries[] = unserialize($query);
        }

        $this->processInstanceId = $data->processInstanceId;
        $this->businessKey = $data->businessKey;
        $this->businessKeyLike = $data->businessKeyLike;
        $this->processDefinitionId = $data->processDefinitionId;
        $this->processInstanceIds = $data->processInstanceIds;
        $this->processDefinitionKey = $data->processDefinitionKey;
        $this->processDefinitionKeys = $data->processDefinitionKeys;
        $this->processDefinitionKeyNotIn = $data->processDefinitionKeyNotIn;
        $this->deploymentId = $data->deploymentId;
        $this->superProcessInstanceId = $data->superProcessInstanceId;
        $this->suspensionState = unserialize($data->suspensionState);
        $this->withIncident = $data->withIncident;
        $this->incidentType = $data->incidentType;
        $this->incidentId = $data->incidentId;
        $this->incidentMessage = $data->incidentMessage;
        $this->incidentMessageLike = $data->incidentMessageLike;
        $this->activityIds = $data->activityIds;
        $this->isRootProcessInstances = $data->isRootProcessInstances;
        $this->isLeafProcessInstances = $data->isLeafProcessInstances;
        $this->isTenantIdSet = $data->isTenantIdSet;
        $this->tenantIds = $data->tenantIds;
        $this->isProcessDefinitionWithoutTenantId = $data->isProcessDefinitionWithoutTenantId;
        $this->queries = $queries;
        $this->isOrQueryActive = $data->isOrQueryActive;
    }

    public function processInstanceId(?string $processInstanceId): ProcessInstanceQueryImpl
    {
        EnsureUtil::ensureNotNull("Process instance id", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processInstanceIds(array $processInstanceIds): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotEmpty("Set of process instance ids", "processInstanceIds", $processInstanceIds);
        $this->processInstanceIds = $processInstanceIds;
        return $this;
    }

    public function processInstanceBusinessKey(?string $businessKey, ?string $processDefinitionKey = null): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Business key", "businessKey", $businessKey);
        $this->businessKey = $businessKey;
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processInstanceBusinessKeyLike(?string $businessKeyLike): ProcessInstanceQueryInterface
    {
        $this->businessKeyLike = $businessKeyLike;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): ProcessInstanceQueryImpl
    {
        EnsureUtil::ensureNotNull("Process definition id", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): ProcessInstanceQueryImpl
    {
        EnsureUtil::ensureNotNull("Process definition key", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKeys", "processDefinitionKeys", $processDefinitionKeys);
        $this->processDefinitionKeys = $processDefinitionKeys;
        return $this;
    }

    public function processDefinitionKeyNotIn(array $processDefinitionKeys): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKeyNotIn", "processDefinitionKeys", $processDefinitionKeys);
        $this->processDefinitionKeyNotIn = $processDefinitionKeys;
        return $this;
    }

    public function deploymentId(?string $deploymentId): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Deployment id", "deploymentId", $deploymentId);
        $this->deploymentId = $deploymentId;
        return $this;
    }

    public function superProcessInstanceId(?string $superProcessInstanceId): ProcessInstanceQueryInterface
    {
        if ($this->isRootProcessInstances) {
            throw new ProcessEngineException("Invalid query usage: cannot set both rootProcessInstances and superProcessInstanceId");
        }
        $this->superProcessInstanceId = $superProcessInstanceId;
        return $this;
    }

    public function subProcessInstanceId(?string $subProcessInstanceId): ProcessInstanceQueryInterface
    {
        $this->subProcessInstanceId = $subProcessInstanceId;
        return $this;
    }

    /*public function caseInstanceId(?string $caseInstanceId): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("caseInstanceId", caseInstanceId);
        $this->caseInstanceId = caseInstanceId;
        return $this;
    }

    public ProcessInstanceQueryInterface superCaseInstanceId(?string $superCaseInstanceId) {
        EnsureUtil::ensureNotNull("superCaseInstanceId", superCaseInstanceId);
        $this->superCaseInstanceId = superCaseInstanceId;
        return $this;
    }

    public ProcessInstanceQueryInterface subCaseInstanceId(?string $subCaseInstanceId) {
        EnsureUtil::ensureNotNull("subCaseInstanceId", subCaseInstanceId);
        $this->subCaseInstanceId = subCaseInstanceId;
        return $this;
    }*/

    public function orderByProcessInstanceId(): ProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceId() within 'or' query");
        }

        $this->orderBy(ProcessInstanceQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionId(): ProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessDefinitionId() within 'or' query");
        }

        $this->orderBy(
            new QueryOrderingProperty(
                QueryOrderingProperty::relationProcessDefinition(),
                ProcessInstanceQueryProperty::processDefinitionId()
            )
        );
        return $this;
    }

    public function orderByProcessDefinitionKey(): ProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessDefinitionKey() within 'or' query");
        }

        $this->orderBy(
            new QueryOrderingProperty(
                QueryOrderingProperty::relationProcessDefinition(),
                ProcessInstanceQueryProperty::processDefinitionKey()
            )
        );
        return $this;
    }

    public function orderByTenantId(): ProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTenantId() within 'or' query");
        }

        $this->orderBy(ProcessInstanceQueryProperty::tenantId());
        return $this;
    }

    public function orderByBusinessKey(): ProcessInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByBusinessKey() within 'or' query");
        }

        $this->orderBy(ProcessInstanceQueryProperty::businessKey());
        return $this;
    }

    public function active(): ProcessInstanceQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function suspended(): ProcessInstanceQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function withIncident(): ProcessInstanceQueryInterface
    {
        $this->withIncident = true;
        return $this;
    }

    public function incidentType(?string $incidentType): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("incident type", "incidentType", $incidentType);
        $this->incidentType = $incidentType;
        return $this;
    }

    public function incidentId(?string $incidentId): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("incident id", "incidentId", $incidentId);
        $this->incidentId = $incidentId;
        return $this;
    }

    public function incidentMessage(?string $incidentMessage): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("incident message", "incidentMessage", $incidentMessage);
        $this->incidentMessage = $incidentMessage;
        return $this;
    }

    public function incidentMessageLike(?string $incidentMessageLike): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("incident messageLike", "incidentMessageLike", $incidentMessageLike);
        $this->incidentMessageLike = $incidentMessageLike;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): ProcessInstanceQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function activityIdIn(array $activityIds): ProcessInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("activity ids", "activityIds", $activityIds);
        $this->activityIds = $activityIds;
        return $this;
    }

    public function rootProcessInstances(): ProcessInstanceQueryInterface
    {
        if ($this->superProcessInstanceId !== null) {
            throw new ProcessEngineException("Invalid query usage: cannot set both rootProcessInstances and superProcessInstanceId");
        }
        $this->isRootProcessInstances = true;
        return $this;
    }

    public function leafProcessInstances(): ProcessInstanceQueryInterface
    {
        if ($this->subProcessInstanceId !== null) {
            throw new ProcessEngineException("Invalid query usage: cannot set both leafProcessInstances and subProcessInstanceId");
        }
        $this->isLeafProcessInstances = true;
        return $this;
    }

    public function processDefinitionWithoutTenantId(): ProcessInstanceQueryInterface
    {
        $this->isProcessDefinitionWithoutTenantId = true;
        return $this;
    }

    //results /////////////////////////////////////////////////////////////////

    protected function checkQueryOk(): void
    {
        $this->ensureVariablesInitialized();

        parent::checkQueryOk();
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();

        return $commandContext
            ->getExecutionManager()
            ->findProcessInstanceCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();

        return $commandContext
            ->getExecutionManager()
            ->findProcessInstancesByQueryCriteria($this, $page);
    }

    public function executeIdsList(CommandContext $commandContext): array
    {
        $this->checkQueryOk();

        return $commandContext
            ->getExecutionManager()
            ->findProcessInstancesIdsByQueryCriteria($this);
    }

    public function executeDeploymentIdMappingsList(CommandContext $commandContext): array
    {
        $this->checkQueryOk();

        return $commandContext
            ->getExecutionManager()
            ->findDeploymentIdMappingsByQueryCriteria($this);
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

    //getters /////////////////////////////////////////////////////////////////

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function addOrQuery(ProcessInstanceQueryImpl $orQuery): void
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

    public function getActivityIds(): array
    {
        return $this->activityIds;
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function getBusinessKeyLike(): ?string
    {
        return $this->businessKeyLike;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionKeys(): array
    {
        return $this->processDefinitionKeys;
    }

    public function getProcessDefinitionKeyNotIn(): array
    {
        return $this->processDefinitionKeyNotIn;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function getSuperProcessInstanceId(): ?string
    {
        return $this->superProcessInstanceId;
    }

    public function getSubProcessInstanceId(): ?string
    {
        return $this->subProcessInstanceId;
    }

    public function getSuspensionState(): SuspensionState
    {
        return $this->suspensionState;
    }

    public function setSuspensionState(SuspensionState $suspensionState): void
    {
        $this->suspensionState = $suspensionState;
    }

    public function isWithIncident(): bool
    {
        return $this->withIncident;
    }

    public function getIncidentId(): ?string
    {
        return $this->incidentId;
    }

    public function getIncidentType(): ?string
    {
        return $this->incidentType;
    }

    public function getIncidentMessage(): ?string
    {
        return $this->incidentMessage;
    }

    public function getIncidentMessageLike(): ?string
    {
        return $this->incidentMessageLike;
    }

    /*public String getCaseInstanceId() {
        return caseInstanceId;
    }

    public String getSuperCaseInstanceId() {
        return superCaseInstanceId;
    }

    public String getSubCaseInstanceId() {
        return subCaseInstanceId;
    }*/

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function isRootProcessInstances(): bool
    {
        return $this->isRootProcessInstances;
    }

    public function isProcessDefinitionWithoutTenantId(): bool
    {
        return $this->isProcessDefinitionWithoutTenantId;
    }

    public function isLeafProcessInstances(): bool
    {
        return $this->isLeafProcessInstances;
    }

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    public function or(): ProcessInstanceQueryInterface
    {
        if ($this != $this->queries[0]) {
            throw new ProcessEngineException("Invalid query usage: cannot set or() within 'or' query");
        }

        $orQuery = new ProcessInstanceQueryImpl();
        $orQuery->isOrQueryActive = true;
        $orQuery->queries = $this->queries;
        $this->queries[] = $orQuery;
        return $orQuery;
    }

    public function endOr(): ProcessInstanceQueryInterface
    {
        if (!empty($this->queries) && $this != $this->queries[count($this->queries) - 1]) {
            throw new ProcessEngineException("Invalid query usage: cannot set endOr() before or()");
        }

        return $this->queries[0];
    }
}
