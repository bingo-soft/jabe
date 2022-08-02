<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Engine\Management\JobDefinitionInterface;

class JobDefinitionEntity implements JobDefinitionInterface, HasDbRevisionInterface, HasDbReferencesInterface, DbEntityInterface, \Serializable
{
    protected $id;
    protected $revision;

    protected $processDefinitionId;
    protected $processDefinitionKey;

    /* Note: this is the id of the activity which is the cause that a Job is created.
     * If the Job corresponds to an event scope, it may or may not correspond to the
     * activity which defines the event scope.
     *
     * Example:
     * user task with attached timer event:
     * - timer event scope = user task
     * - activity which causes the job to be created = timer event.
     * => Job definition activityId will be activityId of the timer event, not the activityId of the user task.
     */
    protected $activityId;

    /** timer, message, ... */
    protected $jobType;
    protected $jobConfiguration;

    // job definition is active by default
    protected $suspensionState;

    protected $jobPriority;

    protected $tenantId;

    protected $deploymentId;

    public function __construct(?JobDeclarationInterface $jobDeclaration = null)
    {
        if ($jobDeclaration !== null) {
            $this->activityId = $jobDeclaration->getActivityId();
            $this->jobConfiguration = $jobDeclaration->getJobConfiguration();
            $this->jobType = $jobDeclaration->getJobHandlerType();
        }
        $this->suspensionState = SuspensionStateImpl::active()->getStateCode();
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'processDefinitionId' => $this->processDefinitionId,
            'processDefinitionKey' => $this->processDefinitionKey,
            'activityId' => $this->activityId,
            'jobType' => $this->jobType,
            'jobConfiguration' => $this->jobConfiguration,
            'jobPriority' => $this->jobPriority,
            'tenantId' => $this->tenantId,
            'deploymentId' => $this->deploymentId,
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->processDefinitionKey = $json->processDefinitionKey;
        $this->activityId = $json->activityId;
        $this->jobType = $json->jobType;
        $this->jobConfiguration = $json->jobConfiguration;
        $this->suspensionState = $json->suspensionState;
        $this->jobPriority = $json->jobPriority;
        $this->tenantId = $json->tenantId;
        $this->deploymentId = $json->deploymentId;
    }

    public function getPersistentState()
    {
        $state = [];
        $state["processDefinitionId"] = $this->processDefinitionId;
        $state["processDefinitionKey"] = $this->processDefinitionKey;
        $state["activityId"] = $this->activityId;
        $state["jobType"] = $this->obType;
        $state["jobConfiguration"] = $this->jobConfiguration;
        $state["suspensionState"] = $this->suspensionState;
        $state["jobPriority"] = $this->jobPriority;
        $state["tenantId"] = $this->tenantId;
        $state["deploymentId"] = $this->deploymentId;
        return $state;
    }

    // getters / setters /////////////////////////////////

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function isSuspended(): bool
    {
        return SuspensionStateImpl::suspended()->getStateCode() == $this->suspensionState;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }

    public function setJobType(string $jobType): void
    {
        $this->jobType = $jobType;
    }

    public function getJobConfiguration(): string
    {
        return $this->jobConfiguration;
    }

    public function setJobConfiguration(string $jobConfiguration): void
    {
        $this->jobConfiguration = $jobConfiguration;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getSuspensionState(): int
    {
        return $this->suspensionState;
    }

    public function setSuspensionState(int $state): void
    {
        $this->suspensionState = $state;
    }

    public function getOverridingJobPriority(): int
    {
        return $this->jobPriority;
    }

    public function setJobPriority(int $jobPriority): void
    {
        $this->jobPriority = $jobPriority;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];
        return $referenceIdAndClass;
    }
}
