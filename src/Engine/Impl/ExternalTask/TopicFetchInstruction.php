<?php

namespace BpmPlatform\Engine\Impl\ExternalTask;

use BpmPlatform\Engine\Impl\{
    QueryOperator,
    QueryVariableValue
};
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Variable\Serializer\VariableSerializersInterface;

class TopicFetchInstruction implements \Serializable
{
    protected $topicName;
    protected $businessKey;
    protected $processDefinitionId;
    protected $processDefinitionIds = [];
    protected $processDefinitionKey;
    protected $processDefinitionKeys = [];
    protected $processDefinitionVersionTag;
    protected $isTenantIdSet = false;
    protected $tenantIds = [];
    protected $variablesToFetch = [];

    protected $filterVariables = [];
    protected $lockDuration;
    protected $deserializeVariables = false;
    protected $localVariables = false;
    protected $includeExtensionProperties = false;

    public function __construct(string $topicName, int $lockDuration)
    {
        $this->topicName = $topicName;
        $this->lockDuration = $lockDuration;
    }

    public function serialize()
    {
        return json_encode([
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
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->topicName = $json->topicName;
        $this->businessKey = $json->businessKey;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->processDefinitionKey = $json->processDefinitionKey;
        $this->processDefinitionKeys = $json->processDefinitionKeys;
        $this->processDefinitionVersionTag = $json->processDefinitionVersionTag;
        $this->isTenantIdSet = $json->isTenantIdSet;
        $this->tenantIds = $json->tenantIds;
        $this->variablesToFetch = $json->variablesToFetch;
        $this->filterVariables = $json->filterVariables;
        $this->lockDuration = $json->lockDuration;
        $this->deserializeVariables = $json->deserializeVariables;
        $this->localVariables = $json->localVariables;
        $this->includeExtensionProperties = $json->includeExtensionProperties;
    }

    public function getVariablesToFetch(): array
    {
        return $this->variablesToFetch;
    }

    public function setVariablesToFetch(array $variablesToFetch): void
    {
        $this->variablesToFetch = $variablesToFetch;
    }

    public function setBusinessKey(string $businessKey): void
    {
        $this->businessKey = $businessKey;
    }

    public function getBusinessKey(): string
    {
        return $this->businessKey;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessDefinitionId(): string
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

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getProcessDefinitionKey(): string
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

    public function setProcessDefinitionVersionTag(string $processDefinitionVersionTag): void
    {
        $this->processDefinitionVersionTag = $processDefinitionVersionTag;
    }

    public function getProcessDefinitionVersionTag(): string
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
        foreach ($this->filterVariables as $key => $value) {
            $variableValue = new QueryVariableValue($key, $value, null, false);
            $this->filterVariables[] = $variableValue;
        }
    }

    public function addFilterVariable(string $name, $value): void
    {
        $variableValue = new QueryVariableValue($name, $value, QueryOperator::EQUALS, true);
        $this->filterVariables[] = $variableValue;
    }

    public function getLockDuration(): int
    {
        return $this->lockDuration;
    }

    public function getTopicName(): string
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
            foreach ($filterVariables as $queryVariableValue) {
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
