<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Management\{
    JobDefinitionInterface,
    JobDefinitionQueryInterface
};

class JobDefinitionQueryImpl extends AbstractQuery implements JobDefinitionQueryInterface, \Serializable
{
    protected $id;
    protected $activityIds = [];
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $jobType;
    protected $jobConfiguration;
    protected $suspensionState;
    protected $withOverridingJobPriority;
    protected bool $isTenantIdSet = false;
    protected $tenantIds = [];
    protected bool $includeJobDefinitionsWithoutTenantId = false;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'activityIds' => $this->activityIds,
            'processDefinitionId' => $this->processDefinitionId,
            'processDefinitionKey' => $this->processDefinitionKey,
            'jobType' => $this->jobType,
            'jobConfiguration' => $this->jobConfiguration,
            'suspensionState' => serialize($this->suspensionState),
            'withOverridingJobPriority' => $this->withOverridingJobPriority,
            'isTenantIdSet' => $this->isTenantIdSet,
            'tenantIds' => $this->tenantIds,
            'includeJobDefinitionsWithoutTenantId' => $this->includeJobDefinitionsWithoutTenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->activityIds = $json->activityIds;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->processDefinitionKey = $json->processDefinitionKey;
        $this->jobType = $json->jobType;
        $this->jobConfiguration = $json->jobConfiguration;
        $this->suspensionState = unserialize($json->suspensionState);
        $this->withOverridingJobPriority = $json->withOverridingJobPriority;
        $this->isTenantIdSet = $json->isTenantIdSet;
        $this->tenantIds = $json->tenantIds;
        $this->includeJobDefinitionsWithoutTenantId = $json->includeJobDefinitionsWithoutTenantId;
    }

    public function jobDefinitionId(?string $jobDefinitionId): JobDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("Job definition id", "jobDefinitionId", $jobDefinitionId);
        $this->id = $jobDefinitionId;
        return $this;
    }

    public function activityIdIn(array $activityIds): JobDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("Activity ids", "activityIds", $activityIds);
        $this->activityIds = $activityIds;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): JobDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("Process definition id", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): JobDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("Process definition key", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function jobType(?string $jobType): JobDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("Job type", "jobType", $jobType);
        $this->jobType = $jobType;
        return $this;
    }

    public function jobConfiguration(?string $jobConfiguration): JobDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("Job configuration", "jobConfiguration", $jobConfiguration);
        $this->jobConfiguration = $jobConfiguration;
        return $this;
    }

    public function active(): JobDefinitionQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function suspended(): JobDefinitionQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function withOverridingJobPriority(): JobDefinitionQueryInterface
    {
        $this->withOverridingJobPriority = true;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): JobDefinitionQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): JobDefinitionQueryInterface
    {
        $this->isTenantIdSet = true;
        $this->tenantIds = null;
        return $this;
    }

    public function includeJobDefinitionsWithoutTenantId(): JobDefinitionQueryInterface
    {
        $this->includeJobDefinitionsWithoutTenantId = true;
        return $this;
    }

    public function orderByJobDefinitionId(): JobDefinitionQueryInterface
    {
        return $this->orderBy(JobDefinitionQueryProperty::jobDefinitionId());
    }

    public function orderByActivityId(): JobDefinitionQueryInterface
    {
        return $this->orderBy(JobDefinitionQueryProperty::activityId());
    }

    public function orderByProcessDefinitionId(): JobDefinitionQueryInterface
    {
        return $this->orderBy(JobDefinitionQueryProperty::processDefinitionId());
    }

    public function orderByProcessDefinitionKey(): JobDefinitionQueryInterface
    {
        return $this->orderBy(JobDefinitionQueryProperty::processDefinitionKey());
    }

    public function orderByJobType(): JobDefinitionQueryInterface
    {
        return $this->orderBy(JobDefinitionQueryProperty::jobType());
    }

    public function orderByJobConfiguration(): JobDefinitionQueryInterface
    {
        return $this->orderBy(JobDefinitionQueryProperty::jobConfiguration());
    }

    public function orderByTenantId(): JobDefinitionQueryInterface
    {
        return $this->orderBy(JobDefinitionQueryProperty::tenantId());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getJobDefinitionManager()
            ->findJobDefinitionCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getJobDefinitionManager()
            ->findJobDefnitionByQueryCriteria($this, $page);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getActivityIds(): array
    {
        return $this->activityIds;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function getJobType(): ?string
    {
        return $this->jobType;
    }

    public function getJobConfiguration(): ?string
    {
        return $this->jobConfiguration;
    }

    public function getSuspensionState(): SuspensionState
    {
        return $this->suspensionState;
    }

    public function getWithOverridingJobPriority(): bool
    {
        return $this->withOverridingJobPriority;
    }
}
