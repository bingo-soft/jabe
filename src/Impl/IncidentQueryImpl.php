<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Runtime\{
    IncidentInterface,
    IncidentQueryInterface
};
use Jabe\Impl\Util\EnsureUtil;

class IncidentQueryImpl extends AbstractQuery implements IncidentQueryInterface
{
    protected $id;
    protected $incidentType;
    protected $incidentMessage;
    protected $incidentMessageLike;
    protected $executionId;
    protected $incidentTimestampBefore;
    protected $incidentTimestampAfter;
    protected $activityId;
    protected $failedActivityId;
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKeys = [];
    protected $causeIncidentId;
    protected $rootCauseIncidentId;
    protected $configuration;
    protected $tenantIds = [];
    protected $jobDefinitionIds = [];

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'incidentType' => $this->incidentType,
            'incidentMessage' => $this->incidentMessage,
            'incidentMessageLike' => $this->incidentMessageLike,
            'executionId' => $this->executionId,
            'incidentTimestampBefore' => $this->incidentTimestampBefore,
            'incidentTimestampAfter' => $this->incidentTimestampAfter,
            'activityId' => $this->activityId,
            'failedActivityId' => $this->failedActivityId,
            'processInstanceId' => $this->processInstanceId,
            'processDefinitionId' => $this->processDefinitionId,
            'processDefinitionKeys' => $this->processDefinitionKeys,
            'causeIncidentId' => $this->causeIncidentId,
            'rootCauseIncidentId' => $this->rootCauseIncidentId,
            'configuration' => $this->configuration,
            'tenantIds' => $this->tenantIds,
            'jobDefinitionIds' => $this->jobDefinitionIds
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->incidentType = $data['incidentType'];
        $this->incidentMessage = $data['incidentMessage'];
        $this->incidentMessageLike = $data['incidentMessageLike'];
        $this->executionId = $data['executionId'];
        $this->incidentTimestampBefore = $data['incidentTimestampBefore'];
        $this->incidentTimestampAfter = $data['incidentTimestampAfter'];
        $this->activityId = $data['activityId'];
        $this->failedActivityId = $data['failedActivityId'];
        $this->processInstanceId = $data['processInstanceId'];
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->processDefinitionKeys = $data['processDefinitionKeys'];
        $this->causeIncidentId = $data['causeIncidentId'];
        $this->rootCauseIncidentId = $data['rootCauseIncidentId'];
        $this->configuration = $data['configuration'];
        $this->tenantIds = $data['tenantIds'];
        $this->jobDefinitionIds = $data['jobDefinitionIds'];
    }

    public function incidentId(?string $incidentId): IncidentQueryInterface
    {
        $this->id = $incidentId;
        return $this;
    }

    public function incidentType(?string $incidentType): IncidentQueryInterface
    {
        $this->incidentType = $incidentType;
        return $this;
    }

    public function incidentMessage(?string $incidentMessage): IncidentQueryInterface
    {
        $this->incidentMessage = $incidentMessage;
        return $this;
    }

    public function incidentMessageLike(?string $incidentMessageLike): IncidentQueryInterface
    {
        $this->incidentMessageLike = $incidentMessageLike;
        return $this;
    }

    public function executionId(?string $executionId): IncidentQueryInterface
    {
        $this->executionId = $executionId;
        return $this;
    }

    public function incidentTimestampBefore(?string $incidentTimestampBefore): IncidentQueryInterface
    {
        $this->incidentTimestampBefore = $incidentTimestampBefore;
        return $this;
    }

    public function incidentTimestampAfter(?string $incidentTimestampAfter): IncidentQueryInterface
    {
        $this->incidentTimestampAfter = $incidentTimestampAfter;
        return $this;
    }

    public function activityId(?string $activityId): IncidentQueryInterface
    {
        $this->activityId = $activityId;
        return $this;
    }

    public function failedActivityId(?string $activityId): IncidentQueryInterface
    {
        $this->failedActivityId = $activityId;
        return $this;
    }

    public function processInstanceId(?string $processInstanceId): IncidentQueryInterface
    {
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): IncidentQueryInterface
    {
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): IncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKeys", "processDefinitionKeys", $processDefinitionKeys);
        $this->processDefinitionKeys = $processDefinitionKeys;
        return $this;
    }

    public function causeIncidentId(?string $causeIncidentId): IncidentQueryInterface
    {
        $this->causeIncidentId = $causeIncidentId;
        return $this;
    }

    public function rootCauseIncidentId(?string $rootCauseIncidentId): IncidentQueryInterface
    {
        $this->rootCauseIncidentId = $rootCauseIncidentId;
        return $this;
    }

    public function configuration(?string $configuration): IncidentQueryInterface
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): IncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        return $this;
    }

    public function jobDefinitionIdIn(array $jobDefinitionIds): IncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("jobDefinitionIds", "jobDefinitionIds", $jobDefinitionIds);
        $this->jobDefinitionIds = $jobDefinitionIds;
        return $this;
    }

    public function orderByIncidentId(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::incidentId());
        return $this;
    }

    public function orderByIncidentTimestamp(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::incidentTimestamp());
        return $this;
    }

    public function orderByIncidentType(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::incidentType());
        return $this;
    }

    public function orderByExecutionId(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::executionId());
        return $this;
    }

    public function orderByActivityId(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::activityId());
        return this;
    }

    public function orderByProcessInstanceId(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionId(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::processDefinitionId());
        return $this;
    }

    public function orderByCauseIncidentId(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::causeIncidentId());
        return $this;
    }

    public function orderByRootCauseIncidentId(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::rootCauseIncidentId());
        return $this;
    }

    public function orderByConfiguration(): IncidentQueryInterface
    {
        $this->orderBy(IncidentQueryProperty::configuration());
        return $this;
    }

    public function orderByTenantId(): IncidentQueryInterface
    {
        return $this->orderBy(IncidentQueryProperty::tenantId());
    }

    public function orderByIncidentMessage(): IncidentQueryInterface
    {
        return $this->orderBy(IncidentQueryProperty::incidentMessage());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getIncidentManager()
            ->findIncidentCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getIncidentManager()
            ->findIncidentByQueryCriteria($this, $page);
    }

    public function getProcessDefinitionKeys(): array
    {
        return $this->processDefinitionKeys;
    }
}
