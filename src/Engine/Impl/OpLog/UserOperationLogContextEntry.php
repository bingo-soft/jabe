<?php

namespace Jabe\Engine\Impl\OpLog;

class UserOperationLogContextEntry
{
    protected $deploymentId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processInstanceId;
    protected $executionId;
    //protected String caseDefinitionId;
    //protected String caseInstanceId;
    //protected String caseExecutionId;
    protected $taskId;
    protected $operationType;
    protected $entityType;
    protected $propertyChanges = [];
    protected $jobDefinitionId;
    protected $jobId;
    protected $batchId;
    protected $category;
    protected $rootProcessInstanceId;
    protected $externalTaskId;
    protected $annotation;

    public function __construct(string $operationType, string $entityType)
    {
        $this->operationType = $operationType;
        $this->entityType = $entityType;
    }

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
    }

    /*public function getCaseDefinitionId(): string
    {
        return $this->caseDefinitionId;
    }

    public void setCaseDefinitionId(string $caseDefinitionId) {
        $this->caseDefinitionId = caseDefinitionId;
    }

    public String getCaseInstanceId() {
        return caseInstanceId;
    }

    public void setCaseInstanceId(string $caseInstanceId) {
        $this->caseInstanceId = caseInstanceId;
    }

    public String getCaseExecutionId() {
        return caseExecutionId;
    }

    public void setCaseExecutionId(string $caseExecutionId) {
        $this->caseExecutionId = caseExecutionId;
    }*/

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): void
    {
        $this->operationType = $operationType;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): void
    {
        $this->entityType = $entityType;
    }

    public function getPropertyChanges(): array
    {
        return $this->propertyChanges;
    }

    public function setPropertyChanges(array $propertyChanges): void
    {
        $this->propertyChanges = $propertyChanges;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    public function setJobDefinitionId(string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function setBatchId(string $batchId): void
    {
        $this->batchId = $batchId;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getExternalTaskId(): string
    {
        return $this->externalTaskId;
    }

    public function setExternalTaskId(string $externalTaskId): void
    {
        $this->externalTaskId = $externalTaskId;
    }

    public function getAnnotation(): string
    {
        return $this->annotation;
    }

    public function setAnnotation(string $annotation): void
    {
        $this->annotation = $annotation;
    }
}
