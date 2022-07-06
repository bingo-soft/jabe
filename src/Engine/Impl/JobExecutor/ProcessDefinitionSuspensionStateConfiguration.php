<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Repository\UpdateProcessDefinitionSuspensionStateBuilderImpl;

class ProcessDefinitionSuspensionStateConfiguration implements JobHandlerConfigurationInterface
{
    protected const JOB_HANDLER_CFG_BY = "by";
    protected const JOB_HANDLER_CFG_PROCESS_DEFINITION_ID = "processDefinitionId";
    protected const JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY = "processDefinitionKey";
    protected const JOB_HANDLER_CFG_PROCESS_DEFINITION_TENANT_ID = "processDefinitionTenantId";
    protected const JOB_HANDLER_CFG_INCLUDE_PROCESS_INSTANCES = "includeProcessInstances";

    protected $processDefinitionKey;
    protected $processDefinitionId;
    protected $includeProcessInstances;
    protected $tenantId;
    protected $isTenantIdSet;
    protected $by;

    public function toCanonicalString(): string
    {
        $json = [];

        $json[self::JOB_HANDLER_CFG_BY] = $this->by;
        $json[self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY] = $this->processDefinitionKey;
        $json[self::JOB_HANDLER_CFG_INCLUDE_PROCESS_INSTANCES] = $this->includeProcessInstances;
        $json[self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID] = $this->processDefinitionId;

        if ($this->isTenantIdSet) {
            if ($this->tenantId !== null) {
                $json[self::JOB_HANDLER_CFG_PROCESS_DEFINITION_TENANT_ID] = $this->tenantId;
            } else {
                $json[self::JOB_HANDLER_CFG_PROCESS_DEFINITION_TENANT_ID] = null;
            }
        }

        return json_encode($json);
    }

    public function __toString()
    {
        return $this->toCanonicalString();
    }

    public function createBuilder(): UpdateProcessDefinitionSuspensionStateBuilderImpl
    {
        $builder = new UpdateProcessDefinitionSuspensionStateBuilderImpl();

        if ($this->by == self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID) {
            $builder->byProcessDefinitionId($this->processDefinitionId);
        } elseif ($by == self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY) {
            $builder->byProcessDefinitionKey($this->processDefinitionKey);

            if ($this->isTenantIdSet) {
                if ($this->tenantId !== null) {
                    $builder->processDefinitionTenantId($this->tenantId);
                } else {
                    $builder->processDefinitionWithoutTenantId();
                }
            }
        } else {
            throw new ProcessEngineException("Unexpected job handler configuration for property '" . self::JOB_HANDLER_CFG_BY . "': " . $this->by);
        }

        $builder->includeProcessInstances($this->includeProcessInstances);

        return $builder;
    }

    public static function fromJson($jsonObject): ProcessDefinitionSuspensionStateConfiguration
    {
        $config = new ProcessDefinitionSuspensionStateConfiguration();

        $config->by = $jsonObject->{self::JOB_HANDLER_CFG_BY};
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID)) {
            $config->processDefinitionId = $jsonObject->{self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID};
        }
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY)) {
            $config->processDefinitionKey = $jsonObject->{self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY};
        }
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_PROCESS_DEFINITION_TENANT_ID)) {
            $config->isTenantIdSet = true;
            $config->tenantId = $jsonObject->{self::JOB_HANDLER_CFG_PROCESS_DEFINITION_TENANT_ID};
        }
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_INCLUDE_PROCESS_INSTANCES)) {
            $config->includeProcessInstances = $jsonObject->{self::JOB_HANDLER_CFG_INCLUDE_PROCESS_INSTANCES};
        }

        return $config;
    }

    public static function byProcessDefinitionId(string $processDefinitionId, bool $includeProcessInstances): ProcessDefinitionSuspensionStateConfiguration
    {
        $configuration = new ProcessDefinitionSuspensionStateConfiguration();

        $configuration->by = self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID;
        $configuration->processDefinitionId = $processDefinitionId;
        $configuration->includeProcessInstances = $includeProcessInstances;

        return $configuration;
    }

    public static function byProcessDefinitionKey(string $processDefinitionKey, bool $includeProcessInstances): ProcessDefinitionSuspensionStateConfiguration
    {
        $configuration = new ProcessDefinitionSuspensionStateConfiguration();

        $configuration->by = self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY;
        $configuration->processDefinitionKey = $processDefinitionKey;
        $configuration->includeProcessInstances = $includeProcessInstances;

        return $configuration;
    }

    public static function byProcessDefinitionKeyAndTenantId(string $processDefinitionKey, ?string $tenantId, bool $includeProcessInstances): ProcessDefinitionSuspensionStateConfiguration
    {
        $configuration = self::byProcessDefinitionKey($processDefinitionKey, $includeProcessInstances);

        $configuration->isTenantIdSet = true;
        $configuration->tenantId = $tenantId;

        return $configuration;
    }
}
