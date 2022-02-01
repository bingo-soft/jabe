<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\EntityTypes;
use BpmPlatform\Engine\Authorization\{
    PermissionInterface,
    Permissions
};
use BpmPlatform\Engine\History\{
    HistoricTaskInstanceInterface,
    UserOperationLogEntryInterface
};
use BpmPlatform\Engine\Impl\{
    Page,
    UserOperationLogQueryImpl
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Db\ListQueryParameterObject;
use BpmPlatform\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use BpmPlatform\Engine\Impl\History\HistoryLevel;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypes,
    UserOperationLogEntryEventEntity
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Identity\IdentityOperationResult;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Oplog\{
    UserOperationLogContext,
    UserOperationLogContextEntryBuilder
};
use BpmPlatform\Engine\Impl\Persistence\AbstractHistoricManager;
use BpmPlatform\Engine\Impl\Repository\ResourceDefinitionEntityInterface;
use BpmPlatform\Engine\Impl\Util\{
    PermissionConverter,
    StringUtil
};

class UserOperationLogManager extends AbstractHistoricManager
{
    public function findOperationLogById(string $entryId): ?UserOperationLogEntryInterface
    {
        return $this->getDbEntityManager()->selectById(UserOperationLogEntryEventEntity::class, $entryId);
    }

    public function findOperationLogEntryCountByQueryCriteria(UserOperationLogQueryImpl $query): int
    {
        $this->getAuthorizationManager()->configureUserOperationLogQuery($query);
        return $this->getDbEntityManager()->selectOne("selectUserOperationLogEntryCountByQueryCriteria", $query);
    }

    public function findOperationLogEntriesByQueryCriteria(UserOperationLogQueryImpl $query, Page $page): array
    {
        $this->getAuthorizationManager()->configureUserOperationLogQuery($query);
        return $this->getDbEntityManager()->selectList("selectUserOperationLogEntriesByQueryCriteria", $query, $page);
    }

    public function addRemovalTimeToUserOperationLogByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(UserOperationLogEntryEventEntity::class, "updateUserOperationLogByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToUserOperationLogByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(UserOperationLogEntryEventEntity::class, "updateUserOperationLogByProcessInstanceId", $parameters);
    }

    public function updateOperationLogAnnotationByOperationId(string $operationId, string $annotation): void
    {
        $parameters = [];
        $parameters["operationId"] = $operationId;
        $parameters["annotation"] = $annotation;

        $this->getDbEntityManager()
            ->updatePreserveOrder(UserOperationLogEntryEventEntity::class, "updateOperationLogAnnotationByOperationId", $parameters);
    }

    public function deleteOperationLogEntryById(string $entryId): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(UserOperationLogEntryEventEntity::class, "deleteUserOperationLogEntryById", $entryId);
        }
    }

    public function deleteOperationLogByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
    {
        $parameters = [];
        $parameters["removalTime"] = $removalTime;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $parameters["minuteFrom"] = $minuteFrom;
            $parameters["minuteTo"] = $minuteTo;
        }
        $parameters["batchSize"] = $batchSize;

        return $this->getDbEntityManager()
            ->deletePreserveOrder(
                UserOperationLogEntryEventEntity::class,
                "deleteUserOperationLogByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }

    public function logUserOperations(UserOperationLogContext $context): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $this->fireUserOperationLog($context);
        }
    }

    public function logUserOperation(string $operation, string $userId): void
    {
        $operationResult = $this->getOperationType($operation);
        $operation = $operationResult ?? $operation;
        if ($operation != null && $this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::USER)
                ->category(UserOperationLogEntryInterface::CATEGORY_ADMIN)
                ->propertyChanges(new PropertyChange("userId", null, $userId));

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logGroupOperation(string $operation, string $groupId): void
    {
        $operationResult = $this->getOperationType($operation);
        $operation = $operationResult ?? $operation;
        if ($operation != null && $this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::GROUP)
                ->category(UserOperationLogEntryInterface::CATEGORY_ADMIN)
                ->propertyChanges(new PropertyChange("groupId", null, $groupId));

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logTenantOperation(string $operation, ?string $tenantId): void
    {
        $operationResult = $this->getOperationType($operation);
        $operation = $operationResult ?? $operation;
        if ($operation != null && $this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::TENANT)
                ->category(UserOperationLogEntryInterface::CATEGORY_ADMIN)
                ->propertyChanges(new PropertyChange("tenantId", null, $tenantId));

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logMembershipOperation(string $operation, ?string $userId, ?string $groupId, ?string $tenantId): void
    {
        $operationResult = $this->getOperationType($operation);
        $operation = $operationResult ?? $operation;
        if ($operation != null && $this->isUserOperationLogEnabled()) {
            $entityType = $tenantId == null ? EntityTypes::GROUP_MEMBERSHIP : EntityTypes::TENANT_MEMBERSHIP;
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, entityType)
                ->category(UserOperationLogEntryInterface::CATEGORY_ADMIN);
            $propertyChanges = [];
            if ($userId != null) {
                $propertyChanges[] = new PropertyChange("userId", null, $userId);
            }
            if ($groupId != null) {
                $propertyChanges[] = new PropertyChange("groupId", null, $groupId);
            }
            if ($tenantId != null) {
                $propertyChanges[] = new PropertyChange("tenantId", null, $tenantId);
            }
            $entryBuilder->propertyChanges($propertyChanges);
            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logTaskOperations(string $operation, HistoricTaskInstanceInterface $task, array $propertyChanges): void
    {
        if ($task instanceof TaskEntity) {
            if ($this->isUserOperationLogEnabled()) {
                $context = new UserOperationLogContext();
                $entryBuilder =
                    UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::TASK)
                    ->category(UserOperationLogEntryInterface::CATEGORY_TASK_WORKER)
                    ->inContextOf($task, $propertyChanges);

                $context->addEntry($entryBuilder->create());
                $this->fireUserOperationLog($context);
            }
        } elseif ($task instanceof HistoricTaskInstanceInterface) {
            if ($this->isUserOperationLogEnabled()) {
                $context = new UserOperationLogContext();
                $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::TASK)
                    ->inContextOf($task, $propertyChanges)
                    ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

                $context->addEntry($entryBuilder->create());
                $this->fireUserOperationLog($context);
            }
        }
    }

    public function logLinkOperation(string $operation, TaskEntity $task, PropertyChange $propertyChange): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::IDENTITY_LINK)
                ->category(UserOperationLogEntryInterface::CATEGORY_TASK_WORKER)
                ->inContextOf($task, [$propertyChange]);

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logProcessInstanceOperation(string $operation, ?string $processInstanceId, ?string $processDefinitionId, ?string $processDefinitionKey, array $propertyChanges, ?string $annotation): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =  UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::PROCESS_INSTANCE)
                ->propertyChanges($propertyChanges)
                ->processInstanceId($processInstanceId)
                ->processDefinitionId($processDefinitionId)
                ->processDefinitionKey($processDefinitionKey)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);
            if ($annotation != null) {
                $entryBuilder->annotation($annotation);
            }

            if ($processInstanceId != null) {
                $instance = $this->getProcessInstanceManager()->findExecutionById($processInstanceId);

                if ($instance != null) {
                    $entryBuilder->inContextOf($instance);
                }
            } elseif ($processDefinitionId != null) {
                $definition = $this->getProcessDefinitionManager()->findLatestProcessDefinitionById($processDefinitionId);
                if ($definition != null) {
                    $entryBuilder->inContextOf($definition);
                }
            }

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logProcessDefinitionOperation(
        string $operation,
        string $processDefinitionId,
        string $processDefinitionKey,
        $propertyChanges
    ): void {
        if ($propertyChanges instanceof PropertyChange) {
            $propertyChanges = [$propertyChanges];
        }
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
            UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::PROCESS_DEFINITION)
                ->propertyChanges($propertyChanges)
                ->processDefinitionId($processDefinitionId)
                ->processDefinitionKey($processDefinitionKey)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            if ($processDefinitionId != null) {
                $definition = $this->getProcessDefinitionManager()->findLatestProcessDefinitionById($processDefinitionId);
                $entryBuilder->inContextOf($definition);
            }

            $context->addEntry($entryBuilder->create());

            $this->fireUserOperationLog($context);
        }
    }

    /*public void logCaseInstanceOperation(string $operation, string $caseInstanceId, List<PropertyChange> propertyChanges) {
        if ($this->isUserOperationLogEnabled()) {

            $context = new UserOperationLogContext();
            $entryBuilder =
            UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::CASE_INSTANCE)
                .caseInstanceId(caseInstanceId)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public void logCaseDefinitionOperation(string $operation, string $caseDefinitionId, List<PropertyChange> propertyChanges) {
        if ($this->isUserOperationLogEnabled()) {

            $context = new UserOperationLogContext();
            $entryBuilder =
            UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::CASE_DEFINITION)
                ->propertyChanges($propertyChanges)
                .caseDefinitionId(caseDefinitionId)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public void logDecisionDefinitionOperation(string $operation, List<PropertyChange> propertyChanges) {
        if ($this->isUserOperationLogEnabled()) {

            $context = new UserOperationLogContext();
            $entryBuilder =
            UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::DECISION_DEFINITION)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }*/

    public function logJobOperation(
        string $operation,
        string $jobId,
        string $jobDefinitionId,
        string $processInstanceId,
        string $processDefinitionId,
        string $processDefinitionKey,
        $propertyChanges
    ): void {
        if ($propertyChanges instanceof PropertyChange) {
            $propertyChanges = [$propertyChanges];
        }
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::JOB)
                ->jobId($jobId)
                ->jobDefinitionId($jobDefinitionId)
                ->processDefinitionId($processDefinitionId)
                ->processDefinitionKey($processDefinitionKey)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            if ($jobId != null) {
                $job = $this->getJobManager()->findJobById($jobId);
                // Backward compatibility
                if ($job != null) {
                    $entryBuilder->inContextOf($job);
                }
            } elseif ($jobDefinitionId != null) {
                $jobDefinition = $this->getJobDefinitionManager()->findById($jobDefinitionId);
                // Backward compatibility
                if ($jobDefinition != null) {
                    $entryBuilder->inContextOf($jobDefinition);
                }
            } elseif ($processInstanceId != null) {
                $processInstance = $this->getProcessInstanceManager()->findExecutionById($processInstanceId);
                // Backward compatibility
                if ($processInstance != null) {
                    $entryBuilder->inContextOf($processInstance);
                }
            } elseif ($processDefinitionId != null) {
                $definition = $this->getProcessDefinitionManager()->findLatestProcessDefinitionById($processDefinitionId);
                // Backward compatibility
                if ($definition != null) {
                    $entryBuilder->inContextOf($definition);
                }
            }

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logJobDefinitionOperation(
        string $operation,
        string $jobDefinitionId,
        string $processDefinitionId,
        string $processDefinitionKey,
        PropertyChange $propertyChange
    ): void {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::JOB_DEFINITION)
                ->jobDefinitionId($jobDefinitionId)
                ->processDefinitionId($processDefinitionId)
                ->processDefinitionKey($processDefinitionKey)
                ->propertyChanges($propertyChange)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            if ($jobDefinitionId != null) {
                $jobDefinition = $this->getJobDefinitionManager()->findById($jobDefinitionId);
                // Backward compatibility
                if ($jobDefinition != null) {
                    $entryBuilder->inContextOf($jobDefinition);
                }
            } elseif ($processDefinitionId != null) {
                $definition = $this->getProcessDefinitionManager()->findLatestProcessDefinitionById($processDefinitionId);
                // Backward compatibility
                if ($definition != null) {
                    $entryBuilder->inContextOf($definition);
                }
            }

            $context->addEntry($entryBuilder->create());

            $this->fireUserOperationLog($context);
        }
    }

    public function logAttachmentOperation(string $operation, ExecutionEntity $inst, PropertyChange $propertyChange): void
    {
        if ($inst instanceof TaskEntity) {
            if ($this->isUserOperationLogEnabled()) {
                $context = new UserOperationLogContext();

                $entryBuilder =
                    UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::ATTACHMENT)
                    ->category(UserOperationLogEntryInterface::CATEGORY_TASK_WORKER)
                    ->inContextOf($inst, [$propertyChange]);
                $context->addEntry($entryBuilder->create());

                $this->fireUserOperationLog($context);
            }
        } elseif ($inst instanceof ExecutionEntity) {
            if ($this->isUserOperationLogEnabled()) {
                $context = new UserOperationLogContext();

                $entryBuilder =
                    UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::ATTACHMENT)
                    ->category(UserOperationLogEntryInterface::CATEGORY_TASK_WORKER)
                    ->inContextOf($inst, [$propertyChange]);
                $context->addEntry($entryBuilder->create());

                $this->fireUserOperationLog($context);
            }
        }
    }

    public function logVariableOperation(string $operation, string $executionId, string $taskId, PropertyChange $propertyChange): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();

            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::VARIABLE)
                ->propertyChanges($propertyChange);

            if ($executionId != null) {
                $execution = $this->getProcessInstanceManager()->findExecutionById($executionId);
                $entryBuilder->inContextOf($execution)
                    ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);
            } elseif ($taskId != null) {
                $task = $this->getTaskManager()->findTaskById($taskId);
                $entryBuilder->inContextOf($task, [$propertyChange])
                    ->category(UserOperationLogEntryInterface::CATEGORY_TASK_WORKER);
            }

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logHistoricVariableOperation(
        string $operation,
        HistoricVariableInstanceEntity $inst,
        ResourceDefinitionEntity $definition,
        PropertyChange $propertyChange
    ): void {
        if ($inst instanceof HistoricProcessInstanceEntity) {
            if ($this->isUserOperationLogEnabled()) {
                $context = new UserOperationLogContext();

                $entryBuilder =
                    UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::VARIABLE)
                    ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR)
                    ->propertyChanges($propertyChange)
                    ->inContextOf($inst, $definition, [$propertyChange]);

                $context->addEntry($entryBuilder->create());
                $this->fireUserOperationLog($context);
            }
        } elseif ($inst instanceof HistoricVariableInstanceEntity) {
            if ($this->isUserOperationLogEnabled()) {
                $context = new UserOperationLogContext();

                $entryBuilder =
                    UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::VARIABLE)
                    ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR)
                    ->propertyChanges($propertyChange)
                    ->inContextOf($inst, $definition, [$propertyChange]);

                $context->addEntry($entryBuilder->create());
                $this->fireUserOperationLog($context);
            }
        }
    }

    public function logDeploymentOperation(string $operation, string $deploymentId, array $propertyChanges): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();

            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::DEPLOYMENT)
                ->deploymentId($deploymentId)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logBatchOperation(string $operation, ?string $batchId, $propertyChanges): void
    {
        if ($propertyChanges instanceof PropertyChange) {
            $propertyChanges = [$propertyChanges];
        }
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
            UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::BATCH)
                ->batchId($batchId)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            $context->addEntry($entryBuilder->create());

            $this->fireUserOperationLog($context);
        }
    }

    /*public void logDecisionInstanceOperation(string $operation, List<PropertyChange> propertyChanges) {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
            UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::DECISION_INSTANCE)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            $context->addEntry($entryBuilder->create());

            $this->fireUserOperationLog($context);
        }
    }*/

    public function logExternalTaskOperation(string $operation, ExternalTaskEntity $externalTask, array $propertyChanges): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::EXTERNAL_TASK)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            if ($externalTask != null) {
                $instance = null;
                $definition = null;
                if ($externalTask->getProcessInstanceId() != null) {
                    $instance = $this->getProcessInstanceManager()->findExecutionById($externalTask->getProcessInstanceId());
                } elseif ($externalTask->getProcessDefinitionId() != null) {
                    $definition = $this->getProcessDefinitionManager()->findLatestProcessDefinitionById($externalTask->getProcessDefinitionId());
                }
                $entryBuilder->processInstanceId($externalTask->getProcessInstanceId())
                    ->processDefinitionId($externalTask->getProcessDefinitionId())
                    ->processDefinitionKey($externalTask->getProcessDefinitionKey())
                    ->inContextOf($externalTask, $instance, $definition);
            }

            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logMetricsOperation(string $operation, array $propertyChanges): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::METRICS)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);
            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logTaskMetricsOperation(string $operation, array $propertyChanges): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::TASK_METRICS)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);
            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logFilterOperation(string $operation, string $filterId): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::FILTER)
                ->propertyChanges(new PropertyChange("filterId", null, $filterId))
                ->category(UserOperationLogEntryInterface::CATEGORY_TASK_WORKER);
            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logPropertyOperation(string $operation, array $propertyChanges): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::PROPERTY)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_ADMIN);
            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    public function logSetAnnotationOperation(string $operationId): void
    {
        $this->logAnnotationOperation($operationId, EntityTypes::OPERATION_LOG, "operationId", UserOperationLogEntryInterface::OPERATION_TYPE_SET_ANNOTATION);
    }

    public function logClearAnnotationOperation(string $operationId): void
    {
        $this->logAnnotationOperation(
            $operationId,
            EntityTypes::OPERATION_LOG,
            "operationId",
            UserOperationLogEntryInterface::OPERATION_TYPE_CLEAR_ANNOTATION
        );
    }

    public function logSetIncidentAnnotationOperation(string $incidentId): void
    {
        $this->logAnnotationOperation($incidentId, EntityTypes::INCIDENT, "incidentId", UserOperationLogEntryInterface::OPERATION_TYPE_SET_ANNOTATION);
    }

    public function logClearIncidentAnnotationOperation(string $incidentId): void
    {
        $this->logAnnotationOperation(incidentId, EntityTypes::INCIDENT, "incidentId", UserOperationLogEntryInterface::OPERATION_TYPE_CLEAR_ANNOTATION);
    }

    protected function logAnnotationOperation(string $id, string $type, string $idProperty, string $operationType): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operationType, $type)
                    ->propertyChanges(new PropertyChange($idProperty, null, $id))
                    ->category(UserOperationLogEntryInterface::CATEGORY_OPERATOR);

            $context = new UserOperationLogContext();
            $context->addEntry($entryBuilder->create());

            $this->fireUserOperationLog($context);
        }
    }

    public function logAuthorizationOperation(string $operation, AuthorizationEntity $authorization, ?AuthorizationEntity $previousValues): void
    {
        if ($this->isUserOperationLogEnabled()) {
            $propertyChanges = [];
            $propertyChanges[] = new PropertyChange("permissionBits", $previousValues == null ? null : $previousValues->getPermissions(), $authorization->getPermissions());
            $propertyChanges[] = new PropertyChange("permissions", $previousValues == null ? null : $this->getPermissionStringList($previousValues), $this->getPermissionStringList($authorization));
            $propertyChanges[] = new PropertyChange("type", $previousValues == null ? null : $previousValues->getAuthorizationType(), $authorization->getAuthorizationType());
            $propertyChanges[] = new PropertyChange("resource", $previousValues == null ? null : $this->getResourceName($previousValues->getResourceType()), $this->getResourceName($authorization->getResourceType()));
            $propertyChanges[] = new PropertyChange("resourceId", $previousValues == null ? null : $previousValues->getResourceId(), $authorization->getResourceId());
            if ($authorization->getUserId() != null || ($previousValues != null && $previousValues->getUserId() != null)) {
                $propertyChanges[] = new PropertyChange("userId", $previousValues == null ? null : $previousValues->getUserId(), $authorization->getUserId());
            }
            if ($authorization->getGroupId() != null || ($previousValues != null && $previousValues->getGroupId() != null)) {
                $propertyChanges[] = new PropertyChange("groupId", $previousValues == null ? null : $previousValues->getGroupId(), $authorization->getGroupId());
            }

            $context = new UserOperationLogContext();
            $entryBuilder =
                UserOperationLogContextEntryBuilder::entry($operation, EntityTypes::AUTHORIZATION)
                ->propertyChanges($propertyChanges)
                ->category(UserOperationLogEntryInterface::CATEGORY_ADMIN);
            $context->addEntry($entryBuilder->create());
            $this->fireUserOperationLog($context);
        }
    }

    protected function getPermissionStringList(AuthorizationEntity $authorization): string
    {
        $permissionsForResource = Context::getProcessEngineConfiguration()->getPermissionProvider()->getPermissionsForResource($authorization->getResourceType());
        $permissions = $authorization->getPermissions($permissionsForResource);
        $namesForPermissions = PermissionConverter::getNamesForPermissions($authorization, $permissions);
        if (count($namesForPermissions) == 0) {
            return Permissions::nonde()->getName();
        }
        return StringUtil::trimToMaximumLengthAllowed(StringUtil::join($namesForPermissions));
    }

    protected function getResourceName(int $resourceType): string
    {
        return Context::getProcessEngineConfiguration()->getPermissionProvider()->getNameForResource($resourceType);
    }

    public function isUserOperationLogEnabled(): bool
    {
        return $this->isHistoryEventProduced() &&
            (($this->isUserOperationLogEnabledOnCommandContext() && $this->isUserAuthenticated()) ||
                !writeUserOperationLogOnlyWithLoggedInUser());
    }

    protected function isHistoryEventProduced(): bool
    {
        $historyLevel = Context::getProcessEngineConfiguration()->getHistoryLevel();
        return $historyLevel->isHistoryEventProduced(HistoryEventTypes::USER_OPERATION_LOG, null);
    }

    protected function isUserAuthenticated(): bool
    {
        $userId = $this->getAuthenticatedUserId();
        return !empty($userId);
    }

    protected function getAuthenticatedUserId(): ?string
    {
        $commandContext = Context::getCommandContext();
        return $commandContext->getAuthenticatedUserId();
    }

    protected function fireUserOperationLog(UserOperationLogContext $context): void
    {
        if ($context->getUserId() == null) {
            $context->setUserId($this->getAuthenticatedUserId());
        }

        HistoryEventProcessor::processHistoryEvents(new class ($context) extends HistoryEventCreator {
            private $context;

            public function __construct(UserOperationLogContext $context)
            {
                $this->context = $context;
            }

            public function createHistoryEvents(HistoryEventProducerInterface $producer): array
            {
                return $producer->createUserOperationLogEvents($context);
            }
        });
    }

    protected function writeUserOperationLogOnlyWithLoggedInUser(): bool
    {
        return Context::getCommandContext()->isRestrictUserOperationLogToAuthenticatedUsers();
    }

    protected function isUserOperationLogEnabledOnCommandContext(): bool
    {
        return Context::getCommandContext()->isUserOperationLogEnabled();
    }

    protected function getOperationType(string $operationResult): ?string
    {
        switch ($operationResult->getOperation()) {
            case IdentityOperationResult::OPERATION_CREATE:
                return UserOperationLogEntryInterface::OPERATION_TYPE_CREATE;
            case IdentityOperationResult::OPERATION_UPDATE:
                return UserOperationLogEntryInterface::OPERATION_TYPE_UPDATE;
            case IdentityOperationResult::OPERATION_DELETE:
                return UserOperationLogEntryInterface::OPERATION_TYPE_DELETE;
            case IdentityOperationResult::OPERATION_UNLOCK:
                return UserOperationLogEntryInterface::OPERATION_TYPE_UNLOCK;
            default:
                return null;
        }
    }
}
