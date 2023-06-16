<?php

namespace Jabe\Impl\History\Event;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Util\ClassNameUtil;

class UserOperationLogEntryEventEntity extends HistoryEvent implements UserOperationLogEntryInterface
{
    protected $operationId;
    protected $operationType;
    protected $jobId;
    protected $jobDefinitionId;
    protected $taskId;
    protected $userId;
    protected $timestamp;
    protected $property;
    protected $orgValue;
    protected $newValue;
    protected $entityType;
    protected $deploymentId;
    protected $tenantId;
    protected $batchId;
    protected $category;
    protected $externalTaskId;
    protected $annotation;

    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    public function getOperationType(): ?string
    {
        return $this->operationType;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function getOrgValue(): ?string
    {
        return $this->orgValue;
    }

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    public function setOperationId(?string $operationId): void
    {
        $this->operationId = $operationId;
    }

    public function setOperationType(?string $operationType): void
    {
        $this->operationType = $operationType;
    }

    public function setTaskId(?string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function setTimestamp(?string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function setProperty(?string $property): void
    {
        $this->property = $property;
    }

    public function setOrgValue(?string $orgValue): void
    {
        $this->orgValue = $orgValue;
    }

    public function setNewValue(?string $newValue): void
    {
        $this->newValue = $newValue;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): void
    {
        $this->entityType = $entityType;
    }

    public function getJobId(): ?string
    {
        return $this->jobId;
    }

    public function setJobId(?string $jobId): void
    {
        $this->jobId = $jobId;
    }

    public function getJobDefinitionId(): ?string
    {
        return $this->jobDefinitionId;
    }

    public function setJobDefinitionId(?string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(?string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getBatchId(): ?string
    {
        return $this->batchId;
    }

    public function setBatchId(?string $batchId): void
    {
        $this->batchId = $batchId;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getRootProcessInstanceId(): ?string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(?string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getExternalTaskId(): ?string
    {
        return $this->externalTaskId;
    }

    public function setExternalTaskId(?string $externalTaskId): void
    {
        $this->externalTaskId = $externalTaskId;
    }

    public function getAnnotation(): ?string
    {
        return $this->annotation;
    }

    public function setAnnotation(?string $annotation): void
    {
        $this->annotation = $annotation;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'eventType' => $this->eventType,
            'executionId' => $this->executionId,
            'processDefinitionId' => $this->processDefinitionId,
            'processInstanceId' => $this->processInstanceId,
            'rootProcessInstanceId' => $this->rootProcessInstanceId,
            'tenantId' => $this->tenantId,
            'taskId' => $this->taskId,
            'deploymentId' => $this->deploymentId,
            'processDefinitionKey' => $this->processDefinitionKey,
            'jobId' => $this->jobId,
            'jobDefinitionId' => $this->jobDefinitionId,
            'batchId' => $this->batchId,
            'operationId' => $this->operationId,
            'operationType' => $this->operationType,
            'userId' => $this->userId,
            'timestamp' => $this->timestamp,
            'property' => $this->property,
            'orgValue' => $this->orgValue,
            'newValue' => $this->newValue,
            'externalTaskId' => $this->externalTaskId,
            'entityType' => $this->entityType,
            'category' => $this->category,
            'annotation' => $this->annotation
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->eventType = $data['eventType'];
        $this->executionId = $data['executionId'];
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->processInstanceId = $data['processInstanceId'];
        $this->rootProcessInstanceId = $data['rootProcessInstanceId'];
        $this->tenantId = $data['tenantId'];
        $this->taskId = $data['taskId'];
        $this->deploymentId = $data['deploymentId'];
        $this->processDefinitionKey = $data['processDefinitionKey'];
        $this->jobId = $data['jobId'];
        $this->jobDefinitionId = $data['jobDefinitionId'];
        $this->batchId = $data['batchId'];
        $this->operationId = $data['operationId'];
        $this->operationType = $data['operationType'];
        $this->userId = $data['userId'];
        $this->timestamp = $data['timestamp'];
        $this->property = $data['property'];
        $this->orgValue = $data['orgValue'];
        $this->newValue = $data['newValue'];
        $this->externalTaskId = $data['externalTaskId'];
        $this->entityType = $data['entityType'];
        $this->category = $data['category'];
        $this->annotation = $data['annotation'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[taskId" . $this->taskId
            . ", deploymentId" . $this->deploymentId
            . ", processDefinitionKey =" . $this->processDefinitionKey
            . ", jobId = " . $this->jobId
            . ", jobDefinitionId = " . $this->jobDefinitionId
            . ", batchId = " . $this->batchId
            . ", operationId =" . $this->operationId
            . ", operationType =" . $this->operationType
            . ", userId =" . $this->userId
            . ", timestamp =" . $this->timestamp
            . ", property =" . $this->property
            . ", orgValue =" . $this->orgValue
            . ", newValue =" . $this->newValue
            . ", id=" . $this->id
            . ", eventType=" . $this->eventType
            . ", executionId=" . $this->executionId
            . ", processDefinitionId=" . $this->processDefinitionId
            . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
            . ", processInstanceId=" . $this->processInstanceId
            . ", externalTaskId=" . $this->externalTaskId
            . ", tenantId=" . $this->tenantId
            . ", entityType=" . $this->entityType
            . ", category=" . $this->category
            . ", annotation=" . $this->annotation
            . "]";
    }
}
