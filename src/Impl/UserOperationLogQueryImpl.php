<?php

namespace Jabe\Impl;

use Jabe\History\{
    UserOperationLogEntryInterface,
    UserOperationLogQueryInterface
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\{
    CompareUtil,
    EnsureUtil
};

class UserOperationLogQueryImpl extends AbstractQuery implements UserOperationLogQueryInterface
{
    protected $deploymentId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processInstanceId;
    protected $executionId;
    //protected $caseDefinitionId;
    //protected $caseInstanceId;
    //protected $caseExecutionId;
    protected $taskId;
    protected $jobId;
    protected $jobDefinitionId;
    protected $batchId;
    protected $userId;
    protected $operationId;
    protected $externalTaskId;
    protected $operationType;
    protected $property;
    protected $entityType;
    protected $category;
    protected $timestampAfter;
    protected $timestampBefore;

    protected $entityTypes = [];
    protected $categories = [];

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function deploymentId(?string $deploymentId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("deploymentId", "deploymentId", $deploymentId);
        $this->deploymentId = $deploymentId;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKey", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processInstanceId(?string $processInstanceId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceId", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function executionId(?string $executionId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $executionId);
        $this->executionId = $executionId;
        return $this;
    }

    /*public function caseDefinitionId(?string $caseDefinitionId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("caseDefinitionId", caseDefinitionId);
        $this->caseDefinitionId = caseDefinitionId;
        return $this;
    }

    public UserOperationLogQuery caseInstanceId(?string $caseInstanceId) {
        EnsureUtil::ensureNotNull("caseInstanceId", caseInstanceId);
        $this->caseInstanceId = caseInstanceId;
        return $this;
    }

    public UserOperationLogQuery caseExecutionId(?string $caseExecutionId) {
        EnsureUtil::ensureNotNull("caseExecutionId", caseExecutionId);
        $this->caseExecutionId = caseExecutionId;
        return $this;
    }*/

    public function taskId(?string $taskId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $taskId);
        $this->taskId = $taskId;
        return $this;
    }

    public function jobId(?string $jobId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("jobId", "jobId", $jobId);
        $this->jobId = $jobId;
        return $this;
    }

    public function jobDefinitionId(?string $jobDefinitionId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("jobDefinitionId", "jobDefinitionId", $jobDefinitionId);
        $this->jobDefinitionId = $jobDefinitionId;
        return $this;
    }

    public function batchId(?string $batchId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("batchId", "batchId", $batchId);
        $this->batchId = $batchId;
        return $this;
    }

    public function userId(?string $userId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("userId", "userId", $userId);
        $this->userId = $userId;
        return $this;
    }

    public function operationId(?string $operationId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("operationId", "operationId", $operationId);
        $this->operationId = $operationId;
        return $this;
    }

    public function externalTaskId(?string $externalTaskId): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("externalTaskId", "externalTaskId", $externalTaskId);
        $this->externalTaskId = $externalTaskId;
        return $this;
    }

    public function operationType(?string $operationType): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("operationType", "operationType", $operationType);
        $this->operationType = $operationType;
        return $this;
    }

    public function property(?string $property): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("property", "property", $property);
        $this->property = $property;
        return $this;
    }

    public function entityType(?string $entityType): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("entityType", "entityType", $entityType);
        $this->entityType = $entityType;
        return $this;
    }

    public function entityTypeIn(array $entityTypes): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("entity types", "entityTypes", $entityTypes);
        $this->entityTypes = $entityTypes;
        return $this;
    }

    public function category(?string $category): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("category", "category", $category);
        $this->category = $category;
        return $this;
    }

    public function categoryIn(array $categories): UserOperationLogQueryInterface
    {
        EnsureUtil::ensureNotNull("categories", "categories", $categories);
        $this->categories = $categories;
        return $this;
    }

    public function afterTimestamp(?string $after): UserOperationLogQueryInterface
    {
        $this->timestampAfter = $after;
        return $this;
    }

    public function beforeTimestamp(?string $before): UserOperationLogQueryInterface
    {
        $this->timestampBefore = $before;
        return $this;
    }

    public function orderByTimestamp(): UserOperationLogQueryInterface
    {
        return $this->orderBy(OperationLogQueryProperty::timestamp());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getOperationLogManager()
            ->findOperationLogEntryCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getOperationLogManager()
            ->findOperationLogEntriesByQueryCriteria($this, $page);
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions() || CompareUtil::areNotInAscendingOrder($this->timestampAfter, $this->timestampBefore);
    }
}
