<?php

namespace Jabe\Impl\ExternalTask;

use Jabe\Impl\{
    QueryOperator,
    QueryVariableValue
};
use Jabe\Impl\Context\Context;

class TopicFetchInstruction
{
    protected $topicName;
    protected $businessKey;
    protected $processDefinitionId;
    protected $processDefinitionIds = [];
    protected $processDefinitionKey;
    protected $processDefinitionKeys = [];
    protected $processDefinitionVersionTag;
    protected bool $isTenantIdSet = false;
    protected $tenantIds = [];
    protected $variablesToFetch = [];

    protected $filterVariables = [];
    protected $lockDuration;
    protected bool $deserializeVariables = false;
    protected bool $localVariables = false;
    protected bool $includeExtensionProperties = false;

    public function __construct(?string $topicName, int $lockDuration)
    {
        $this->topicName = $topicName;
        $this->lockDuration = $lockDuration;
    }

    public function __serialize(): array
    {
        return [
            'topicName' => $this->topicName,
            'businessKey' => $this->businessKey,
            'processDefinitionId' => $this->processDefinitionId,
            'processDefinitionKey' => $this->processDefinitionKey,
            'processDefinitionKeys' => $this->processDefinitionKeys,
            'processDefinitionVersionTag' => $this->processDefinitionVersionTag,
            'isTenantIdSet' => $this->isTenantIdSet,
            'tenantIds' => $this->tenantIds,
            'variablesToFetch' => $this->variablesToFetch,
            'filterVariables' => $this->filterVariables,
            'lockDuration' => $this->lockDuration,
            'deserializeVariables' => $this->deserializeVariables,
            'localVariables' => $this->localVariables,
            'includeExtensionProperties' => $this->includeExtensionProperties,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->topicName = $data['topicName'];
        $this->businessKey = $data['businessKey'];
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->processDefinitionKey = $data['processDefinitionKey'];
        $this->processDefinitionKeys = $data['processDefinitionKeys'];
        $this->processDefinitionVersionTag = $data['processDefinitionVersionTag'];
        $this->isTenantIdSet = $data['isTenantIdSet'];
        $this->tenantIds = $data['tenantIds'];
        $this->variablesToFetch = $data['variablesToFetch'];
        $this->filterVariables = $data['filterVariables'];
        $this->lockDuration = $data['lockDuration'];
        $this->deserializeVariables = $data['deserializeVariables'];
        $this->localVariables = $data['localVariables'];
        $this->includeExtensionProperties = $data['includeExtensionProperties'];
    }

    public function getVariablesToFetch(): array
    {
        return $this->variablesToFetch;
    }

    public function setVariablesToFetch(array $variablesToFetch): void
    {
        $this->variablesToFetch = $variablesToFetch;
    }

    public function setBusinessKey(?string $businessKey): void
    {
        $this->businessKey = $businessKey;
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function setProcessDefinitionId(?string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionIds(array $processDefinitionIds): void
    {
        $this->processDefinitionIds = $processDefinitionIds;
    }

    public function getProcessDefinitionIds(): array
    {
        return $this->processDefinitionIds;
    }

    public function setProcessDefinitionKey(?string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKeys(array $processDefinitionKeys): void
    {
        $this->processDefinitionKeys = $processDefinitionKeys;
    }

    public function getProcessDefinitionKeys(): array
    {
        return $this->processDefinitionKeys;
    }

    public function setProcessDefinitionVersionTag(?string $processDefinitionVersionTag): void
    {
        $this->processDefinitionVersionTag = $processDefinitionVersionTag;
    }

    public function getProcessDefinitionVersionTag(): ?string
    {
        return $this->processDefinitionVersionTag;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function setTenantIdSet(bool $isTenantIdSet): void
    {
        $this->isTenantIdSet = $isTenantIdSet;
    }

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    public function setTenantIds(array $tenantIds): void
    {
        $this->isTenantIdSet = true;
        $this->tenantIds = $tenantIds;
    }

    public function getFilterVariables(): array
    {
        return $this->filterVariables;
    }

    public function setFilterVariables(array $filterVariables): void
    {
        foreach ($filterVariables as $key => $value) {
            $variableValue = new QueryVariableValue($key, $value, null, false);
            $this->filterVariables[] = $variableValue;
        }
    }

    public function addFilterVariable(?string $name, $value): void
    {
        $variableValue = new QueryVariableValue($name, $value, QueryOperator::EQUALS, true);
        $this->filterVariables[] = $variableValue;
    }

    public function getLockDuration(): int
    {
        return $this->lockDuration;
    }

    public function getTopicName(): ?string
    {
        return $this->topicName;
    }

    public function isDeserializeVariables(): bool
    {
        return $this->deserializeVariables;
    }

    public function setDeserializeVariables(bool $deserializeVariables): void
    {
        $this->deserializeVariables = $deserializeVariables;
    }

    public function ensureVariablesInitialized(): void
    {
        if (!empty($this->filterVariables)) {
            $processEngineConfiguration = Context::getProcessEngineConfiguration();
            $variableSerializers = $processEngineConfiguration->getVariableSerializers();
            $dbType = $processEngineConfiguration->getDatabaseType();
            foreach ($this->filterVariables as $queryVariableValue) {
                $queryVariableValue->initialize($variableSerializers, $dbType);
            }
        }
    }

    public function isLocalVariables(): bool
    {
        return $this->localVariables;
    }

    public function setLocalVariables(bool $localVariables): void
    {
        $this->localVariables = $localVariables;
    }

    public function isIncludeExtensionProperties(): bool
    {
        return $this->includeExtensionProperties;
    }

    public function setIncludeExtensionProperties(bool $includeExtensionProperties): void
    {
        $this->includeExtensionProperties = $includeExtensionProperties;
    }
}
