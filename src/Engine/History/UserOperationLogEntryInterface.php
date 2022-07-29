<?php

namespace Jabe\Engine\History;

interface UserOperationLogEntryInterface
{
    public const OPERATION_TYPE_ASSIGN = "Assign";
    public const OPERATION_TYPE_CLAIM = "Claim";
    public const OPERATION_TYPE_COMPLETE = "Complete";
    public const OPERATION_TYPE_CREATE = "Create";
    public const OPERATION_TYPE_DELEGATE = "Delegate";
    public const OPERATION_TYPE_DELETE = "Delete";
    public const OPERATION_TYPE_RESOLVE = "Resolve";
    public const OPERATION_TYPE_SET_OWNER = "SetOwner";
    public const OPERATION_TYPE_SET_PRIORITY = "SetPriority";
    public const OPERATION_TYPE_UPDATE = "Update";
    public const OPERATION_TYPE_ACTIVATE = "Activate";
    public const OPERATION_TYPE_SUSPEND = "Suspend";
    public const OPERATION_TYPE_MIGRATE = "Migrate";
    public const OPERATION_TYPE_ADD_USER_LINK = "AddUserLink";
    public const OPERATION_TYPE_DELETE_USER_LINK = "DeleteUserLink";
    public const OPERATION_TYPE_ADD_GROUP_LINK = "AddGroupLink";
    public const OPERATION_TYPE_DELETE_GROUP_LINK = "DeleteGroupLink";
    public const OPERATION_TYPE_SET_DUEDATE = "SetDueDate";
    public const OPERATION_TYPE_RECALC_DUEDATE = "RecalculateDueDate";
    public const OPERATION_TYPE_UNLOCK = "Unlock";
    public const OPERATION_TYPE_EXECUTE = "Execute";
    public const OPERATION_TYPE_EVALUATE = "Evaluate";

    public const OPERATION_TYPE_ADD_ATTACHMENT = "AddAttachment";
    public const OPERATION_TYPE_DELETE_ATTACHMENT = "DeleteAttachment";

    public const OPERATION_TYPE_SUSPEND_JOB_DEFINITION = "SuspendJobDefinition";
    public const OPERATION_TYPE_ACTIVATE_JOB_DEFINITION = "ActivateJobDefinition";
    public const OPERATION_TYPE_SUSPEND_PROCESS_DEFINITION = "SuspendProcessDefinition";
    public const OPERATION_TYPE_ACTIVATE_PROCESS_DEFINITION = "ActivateProcessDefinition";

    public const OPERATION_TYPE_CREATE_HISTORY_CLEANUP_JOB = "CreateHistoryCleanupJobs";
    public const OPERATION_TYPE_UPDATE_HISTORY_TIME_TO_LIVE = "UpdateHistoryTimeToLive";
    public const OPERATION_TYPE_DELETE_HISTORY = "DeleteHistory";

    public const OPERATION_TYPE_MODIFY_PROCESS_INSTANCE = "ModifyProcessInstance";
    public const OPERATION_TYPE_RESTART_PROCESS_INSTANCE  = "RestartProcessInstance";
    public const OPERATION_TYPE_SUSPEND_JOB = "SuspendJob";
    public const OPERATION_TYPE_ACTIVATE_JOB = "ActivateJob";
    public const OPERATION_TYPE_SET_JOB_RETRIES = "SetJobRetries";
    public const OPERATION_TYPE_SET_EXTERNAL_TASK_RETRIES = "SetExternalTaskRetries";
    public const OPERATION_TYPE_SET_VARIABLE = "SetVariable";
    public const OPERATION_TYPE_SET_VARIABLES = "SetVariables";

    public const OPERATION_TYPE_REMOVE_VARIABLE = "RemoveVariable";
    public const OPERATION_TYPE_MODIFY_VARIABLE = "ModifyVariable";

    public const OPERATION_TYPE_SUSPEND_BATCH = "SuspendBatch";
    public const OPERATION_TYPE_ACTIVATE_BATCH = "ActivateBatch";

    public const OPERATION_TYPE_CREATE_INCIDENT = "CreateIncident";

    public const OPERATION_TYPE_SET_REMOVAL_TIME = "SetRemovalTime";

    public const OPERATION_TYPE_SET_ANNOTATION = "SetAnnotation";
    public const OPERATION_TYPE_CLEAR_ANNOTATION = "ClearAnnotation";

    public const CATEGORY_ADMIN = "Admin";
    public const CATEGORY_OPERATOR = "Operator";
    public const CATEGORY_TASK_WORKER = "TaskWorker";

    /** The unique identifier of this log entry. */
    public function getId(): string;

    /** Deployment reference */
    public function getDeploymentId(): ?string;

    /** Process definition reference. */
    public function getProcessDefinitionId(): string;

    /**
     * Key of the process definition this log entry belongs to; <code>null</code> means any.
     */
    public function getProcessDefinitionKey(): ?string;

    /** Root process instance reference. */
    public function getRootProcessInstanceId(): string;

    /** Process instance reference. */
    public function getProcessInstanceId(): string;

    /** Execution reference. */
    public function getExecutionId(): string;

    /** Case definition reference. */
    //public function getCaseDefinitionId(): string;

    /** Case instance reference. */
    //public function getCaseInstanceId(): string;

    /** Case execution reference. */
    //public function getCaseExecutionId(): string;

    /** Task instance reference. */
    public function getTaskId(): string;

    /** Job instance reference. */
    public function getJobId(): string;

    /** Job definition reference. */
    public function getJobDefinitionId(): string;

    /** Batch reference. */
    public function getBatchId(): string;

    /** The User who performed the operation */
    public function getUserId(): string;

    /** Timestamp of this change. */
    public function getTimestamp(): string;

    /**
     * The unique identifier of this operation.
     *
     * If an operation modifies multiple properties, multiple UserOperationLogEntry instances will be
     * created with a common operationId. This allows grouping multiple entries which are part of a composite operation.
     */
    public function getOperationId(): string;

    /** External task reference. */
    public function getExternalTaskId(): string;

    /**
     * Type of this operation, like create, assign, claim and so on.
     *
     * @see #OPERATION_TYPE_ASSIGN and other fields beginning with OPERATION_TYPE
     */
    public function getOperationType(): string;

    /**
     * The type of the entity on which this operation was executed.
     *
     * @see #ENTITY_TYPE_TASK and other fields beginning with ENTITY_TYPE
     */
    public function getEntityType(): string;

    /** The property changed by this operation. */
    public function getProperty(): string;

    /** The original value of the property. */
    public function getOrgValue(): string;

    /** The new value of the property. */
    public function getNewValue(): string;

    /** The time the historic user operation log will be removed. */
    public function getRemovalTime(): string;

    /** The category this entry is associated with */
    public function getCategory(): string;

    /** An arbitrary annotation set by a user for auditing reasons */
    public function getAnnotation(): string;
}
