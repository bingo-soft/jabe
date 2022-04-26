<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;

class JobDefinitionSuspensionStateConfiguration implements JobHandlerConfigurationInterface
{
    protected const JOB_HANDLER_CFG_BY = "by";
    protected const JOB_HANDLER_CFG_JOB_DEFINITION_ID = "jobDefinitionId";
    protected const JOB_HANDLER_CFG_PROCESS_DEFINITION_ID = "processDefinitionId";
    protected const JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY = "processDefinitionKey";
    protected const JOB_HANDLER_CFG_PROCESS_DEFINITION_TENANT_ID = "processDefinitionTenantId";
    protected const JOB_HANDLER_CFG_INCLUDE_JOBS = "includeJobs";

    protected $jobDefinitionId;
    protected $processDefinitionKey;
    protected $processDefinitionId;
    protected $includeJobs;
    protected $tenantId;
    protected $isTenantIdSet;
    protected $by;

    public function toCanonicalString(): string
    {
        $json = [];
        $json[self::JOB_HANDLER_CFG_BY] = $this->by;
        $json[self::JOB_HANDLER_CFG_JOB_DEFINITION_ID] = $this->jobDefinitionId;
        $json[self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY] = $this->processDefinitionKey;
        $json[self::JOB_HANDLER_CFG_INCLUDE_JOBS] = $this->includeJobs;
        $json[self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID] = $this->processDefinitionId;

        if ($this->isTenantIdSet) {
            if ($this->tenantId != null) {
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

    public function createBuilder(): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        $builder = new UpdateJobDefinitionSuspensionStateBuilderImpl();

        if (self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID == $this->by) {
            $builder->byProcessDefinitionId($this->processDefinitionId);
        } elseif (self::JOB_HANDLER_CFG_JOB_DEFINITION_ID == $this->by) {
            $builder->byJobDefinitionId($this->jobDefinitionId);
        } elseif (self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY == $this->by) {
            $builder->byProcessDefinitionKey($this->processDefinitionKey);

            if ($this->isTenantIdSet) {
                if ($this->tenantId != null) {
                    $builder->processDefinitionTenantId($this->tenantId);
                } else {
                    $builder->processDefinitionWithoutTenantId();
                }
            }
        } else {
            throw new ProcessEngineException("Unexpected job handler configuration for property '" . self::JOB_HANDLER_CFG_BY . "': " . $this->by);
        }

        $builder->includeJobs($this->includeJobs);

        return $builder;
    }

    public static function fromJson($jsonObject): JobDefinitionSuspensionStateConfiguration
    {
        $config = new JobDefinitionSuspensionStateConfiguration();

        $config->by = $jsonObject->JOB_HANDLER_CFG_BY;
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_JOB_DEFINITION_ID)) {
            $config->jobDefinitionId = $jsonObject->{self::JOB_HANDLER_CFG_JOB_DEFINITION_ID};
        }
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID)) {
            $config->processDefinitionId = $jsonObject->{self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID};
        }
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY)) {
            $config->processDefinitionKey = $jsonObject->{self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY};
        }
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_PROCESS_DEFINITION_TENANT_ID)) {
            $config->isTenantIdSet = true;
            $config->tenantId = $jsonObject->{self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY};
        }
        if (property_exists($jsonObject, self::JOB_HANDLER_CFG_INCLUDE_JOBS)) {
            $config->includeJobs = $jsonObject->{self::JOB_HANDLER_CFG_INCLUDE_JOBS};
        }

        return $config;
    }

    public static function byJobDefinitionId(string $jobDefinitionId, bool $includeJobs): JobDefinitionSuspensionStateConfiguration
    {
        $configuration = new JobDefinitionSuspensionStateConfiguration();
        $configuration->by = self::JOB_HANDLER_CFG_JOB_DEFINITION_ID;
        $configuration->jobDefinitionId = $jobDefinitionId;
        $configuration->includeJobs = $includeJobs;

        return $configuration;
    }

    public static function byProcessDefinitionId(string $processDefinitionId, bool $includeJobs): JobDefinitionSuspensionStateConfiguration
    {
        $configuration = new JobDefinitionSuspensionStateConfiguration();

        $configuration->by = self::JOB_HANDLER_CFG_PROCESS_DEFINITION_ID;
        $configuration->processDefinitionId = self::processDefinitionId;
        $configuration->includeJobs = self::includeJobs;

        return $configuration;
    }

    public static function byProcessDefinitionKey(string $processDefinitionKey, bool $includeJobs): JobDefinitionSuspensionStateConfiguration
    {
        $configuration = new JobDefinitionSuspensionStateConfiguration();

        $configuration->by = self::JOB_HANDLER_CFG_PROCESS_DEFINITION_KEY;
        $configuration->processDefinitionKey = self::processDefinitionKey;
        $configuration->includeJobs = self::includeJobs;

        return $configuration;
    }

    public static function byProcessDefinitionKeyAndTenantId(string $processDefinitionKey, ?string $tenantId, bool $includeProcessInstances): JobDefinitionSuspensionStateConfiguration
    {
        $configuration = self::byProcessDefinitionKey($processDefinitionKey, $includeProcessInstances);

        $configuration->isTenantIdSet = true;
        $configuration->tenantId = $tenantId;

        return $configuration;
    }
}
