<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\History\{
    HistoricVariableInstanceInterface,
    HistoricVariableInstanceQueryInterface
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Persistence\Entity\HistoricVariableInstanceEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Impl\Variable\Serializer\{
    AbstractTypedValueSerializer,
    VariableSerializersInterface
};

class HistoricVariableInstanceQueryImpl extends AbstractQuery implements HistoricVariableInstanceQuery
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $variableNameIn = [];
    protected $variableId;
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $caseInstanceId;
    protected $variableName;
    protected $variableNameLike;
    protected $queryVariableValue;
    protected $variableNamesIgnoreCase;
    protected $variableValuesIgnoreCase;
    protected $variableTypes = [];
    protected $taskIds = [];
    protected $executionIds = [];
    //protected String[] caseExecutionIds;
    //protected String[] caseActivityIds;
    protected $activityInstanceIds = [];

    protected $tenantIds = [];
    protected $isTenantIdSet;

    protected $processInstanceIds = [];
    protected $includeDeleted = false;

    protected $isByteArrayFetchingEnabled = true;
    protected $isCustomObjectDeserializationEnabled = true;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function variableNameIn(array $names): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Variable names", "names", $names);
        $this->variableNameIn = $names;
        return $this;
    }

    public function variableId(string $id): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("variableId", "variableId", $id);
        $this->variableId = $id;
        return $this;
    }

    public function processInstanceId(string $processInstanceId): HistoricVariableInstanceQueryImpl
    {
        EnsureUtil::ensureNotNull("processInstanceId", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(string $processDefinitionKey): HistoricVariableInstanceQueryInterface
    {
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    /*public HistoricVariableInstanceQueryInterface caseInstanceId(string $caseInstanceId) {
        EnsureUtil::ensureNotNull("caseInstanceId", caseInstanceId);
        $this->caseInstanceId = caseInstanceId;
        return $this;
    }*/

    public function variableTypeIn(array $variableTypes): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Variable types", "variableTypes", $variableTypes);
        $this->variableTypes = $this->lowerCase($variableTypes);
        return $this;
    }

    public function matchVariableNamesIgnoreCase(): HistoricVariableInstanceQueryInterface
    {
        $this->variableNamesIgnoreCase = true;
        if ($this->queryVariableValue !== null) {
            $this->queryVariableValue->variableNameIgnoreCase = true;
        }
        return $this;
    }

    public function matchVariableValuesIgnoreCase(): HistoricVariableInstanceQueryInterface
    {
        $this->variableValuesIgnoreCase = true;
        if ($this->queryVariableValue !== null) {
            $this->queryVariableValue->variableValueIgnoreCase = true;
        }
        return $this;
    }

    private function lowerCase(array $variableTypes): array
    {
        for ($i = 0; $i < count($variableTypes); $i += 1) {
            $variableTypes[$i] = strtolower($variableTypes[$i]);
        }
        return $variableTypes;
    }

    /** Only select historic process variables with the given process instance ids. */
    public function processInstanceIdIn(array $processInstanceIds): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Process Instance Ids", "processInstanceIds", $processInstanceIds);
        $this->processInstanceIds = $processInstanceIds;
        return $this;
    }

    public function taskIdIn(array $taskIds): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Task Ids", "taskIds", $taskIds);
        $this->taskIds = $taskIds;
        return $this;
    }

    public function executionIdIn(array $executionIds): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Execution Ids", "executionIds", $executionIds);
        $this->executionIds = $executionIds;
        return $this;
    }

    /*public function caseExecutionIdIn(array $caseExecutionIds) {
        EnsureUtil::ensureNotNull("Case execution ids", (Object[]) caseExecutionIds);
        $this->caseExecutionIds = caseExecutionIds;
        return $this;
    }

    public HistoricVariableInstanceQueryInterface caseActivityIdIn(array $caseActivityIds) {
        EnsureUtil::ensureNotNull("Case activity ids", (Object[]) caseActivityIds);
        $this->caseActivityIds = caseActivityIds;
        return $this;
    }*/

    public function activityInstanceIdIn(array $activityInstanceIds): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("Activity Instance Ids", "activityInstanceIds", $activityInstanceIds);
        $this->activityInstanceIds = $activityInstanceIds;
        return $this;
    }

    public function variableName(string $variableName): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $this->variableName = $variableName;
        return $this;
    }

    public function variableValueEquals(string $variableName, $variableValue): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        EnsureUtil::ensureNotNull("variableValue", "variableValue", $variableValue);
        $this->variableName = $variableName;
        $this->queryVariableValue = new QueryVariableValue($variableName, $variableValue, QueryOperator::EQUALS, true, $this->variableNamesIgnoreCase, $this->variableValuesIgnoreCase);
        return $this;
    }

    public function variableNameLike(string $variableNameLike): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("variableNameLike", "variableNameLike", $variableNameLike);
        $this->variableNameLike = $variableNameLike;
        return $this;
    }

    protected function ensureVariablesInitialized(): void
    {
        if ($this->queryVariableValue !== null) {
            $processEngineConfiguration = Context::getProcessEngineConfiguration();
            $variableSerializers = $processEngineConfiguration->getVariableSerializers();
            $dbType = $processEngineConfiguration->getDatabaseType();
            $this->queryVariableValue->initialize($variableSerializers, $dbType);
        }
    }

    public function disableBinaryFetching(): HistoricVariableInstanceQueryInterface
    {
        $this->isByteArrayFetchingEnabled = false;
        return $this;
    }

    public function disableCustomObjectDeserialization(): HistoricVariableInstanceQueryInterface
    {
        $this->isCustomObjectDeserializationEnabled = false;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): HistoricVariableInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricVariableInstanceQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        $this->ensureVariablesInitialized();
        return $commandContext->getHistoricVariableInstanceManager()->findHistoricVariableInstanceCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        $this->ensureVariablesInitialized();
        $historicVariableInstances = $commandContext
                ->getHistoricVariableInstanceManager()
                ->findHistoricVariableInstancesByQueryCriteria($this, $page);

        if (!empty($historicVariableInstances)) {
            foreach ($historicVariableInstances as $historicVariableInstance) {
                $variableInstanceEntity = $historicVariableInstance;
                if ($this->shouldFetchValue($variableInstanceEntity)) {
                    try {
                        $variableInstanceEntity->getTypedValue($this->isCustomObjectDeserializationEnabled);
                    } catch (\Exception $t) {
                        // do not fail if one of the variables fails to load
                        //LOG.exceptionWhileGettingValueForVariable(t);
                    }
                }
            }
        }
        return $historicVariableInstances;
    }

    protected function shouldFetchValue(HistoricVariableInstanceEntity $entity): bool
    {
        // do not fetch values for byte arrays eagerly (unless requested by the user)
        return $this->isByteArrayFetchingEnabled
            || !in_array($entity->getSerializer()->getType()->getName(), AbstractTypedValueSerializer::BINARY_VALUE_TYPES);
    }

    // order by /////////////////////////////////////////////////////////////////

    public function orderByProcessInstanceId(): HistoricVariableInstanceQueryInterface
    {
        $this->orderBy(HistoricVariableInstanceQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByVariableName(): HistoricVariableInstanceQueryInterface
    {
        $this->orderBy(HistoricVariableInstanceQueryProperty::variableName());
        return $this;
    }

    public function orderByTenantId(): HistoricVariableInstanceQueryInterface
    {
        $this->orderBy(HistoricVariableInstanceQueryProperty::tenantId());
        return $this;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    /*public String getCaseInstanceId() {
        return caseInstanceId;
    }*/

    public function getActivityInstanceIds(): array
    {
        return $this->activityInstanceIds;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getTaskIds(): array
    {
        return $this->taskIds;
    }

    public function getExecutionIds(): array
    {
        return $this->executionIds;
    }

    /*public String[] getCaseExecutionIds() {
        return caseExecutionIds;
    }

    public function getCaseActivityIds(): array
    {
        return caseActivityIds;
    }*/

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariableNameLike(): string
    {
        return $this->variableNameLike;
    }

    public function getQueryVariableValue(): QueryVariableValue
    {
        return $this->queryVariableValue;
    }

    public function getVariableNamesIgnoreCase(): bool
    {
        return $this->variableNamesIgnoreCase;
    }

    public function getVariableValuesIgnoreCase(): bool
    {
        return $this->variableValuesIgnoreCase;
    }

    public function includeDeleted(): HistoricVariableInstanceQueryInterface
    {
        $this->includeDeleted = true;
        return $this;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function getVariableNameIn(): array
    {
        return $this->variableNameIn;
    }
}
