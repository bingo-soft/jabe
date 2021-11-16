<?php

namespace BpmPlatform\Engine\Impl\History\Event;

class HistoryEventTypes implements HistoryEventTypeInterface
{
    /** fired when a process instance is started. */
    public const PROCESS_INSTANCE_START = ["process-instance", "start"];
    /** fired when a process instance is updated */
    public const PROCESS_INSTANCE_UPDATE = ["process-instance-update", "update"];
    /** fired when a process instance is migrated */
    public const PROCESS_INSTANCE_MIGRATE = ["process-instance", "migrate"];
    /** fired when a process instance is ended. */
    public const PROCESS_INSTANCE_END = ["process-instance", "end"];

    /** fired when an activity instance is started. */
    public const ACTIVITY_INSTANCE_START = ["activity-instance", "start"];
    /** fired when an activity instance is updated. */
    public const ACTIVITY_INSTANCE_UPDATE = ["activity-instance", "update"];
    /** fired when an activity instance is migrated. */
    public const ACTIVITY_INSTANCE_MIGRATE = ["activity-instance", "migrate"];
    /** fired when an activity instance is ended. */
    public const ACTIVITY_INSTANCE_END = ["activity-instance", "end"];

    /** fired when a task instance is created. */
    public const TASK_INSTANCE_CREATE = ["task-instance", "create"];
    /** fired when a task instance is updated. */
    public const TASK_INSTANCE_UPDATE = ["task-instance", "update"];
    /** fired when a task instance is migrated. */
    public const TASK_INSTANCE_MIGRATE = ["task-instance", "migrate"];
    /** fired when a task instance is completed. */
    public const TASK_INSTANCE_COMPLETE = ["task-instance", "complete"];
    /** fired when a task instance is deleted. */
    public const TASK_INSTANCE_DELETE = ["task-instance", "delete"];

    /** fired when a variable instance is created. */
    public const VARIABLE_INSTANCE_CREATE = ["variable-instance", "create"];
    /** fired when a variable instance is updated. */
    public const VARIABLE_INSTANCE_UPDATE = ["variable-instance", "update"];
    /** fired when a variable instance is migrated. */
    public const VARIABLE_INSTANCE_MIGRATE = ["variable-instance", "migrate"];
    /** fired when a variable instance is updated. */
    public const VARIABLE_INSTANCE_UPDATE_DETAIL = ["variable-instance", "update-detail"];
    /** fired when a variable instance is deleted. */
    public const VARIABLE_INSTANCE_DELETE = ["variable-instance", "delete"];

    /** fired when a form property is updated. */
    public const FORM_PROPERTY_UPDATE = ["form-property", "form-property-update"];

    /** fired when an incident is created. */
    public const INCIDENT_CREATE = ["incident", "create"];
    /** fired when an incident is migrated. */
    public const INCIDENT_MIGRATE = ["incident", "migrate"];
    /** fired when an incident is deleted. */
    public const INCIDENT_DELETE = ["incident", "delete"];
    /** fired when an incident is resolved. */
    public const INCIDENT_RESOLVE = ["incident", "resolve"];
    /** fired when an incident is updated. */
    public const INCIDENT_UPDATE = ["incident", "update"];

    /** fired when a case instance is created. */
    public const CASE_INSTANCE_CREATE = ["case-instance", "create"];
    /** fired when a case instance is updated. */
    public const CASE_INSTANCE_UPDATE = ["case-instance", "update"];
    /** fired when a case instance is closed. */
    public const CASE_INSTANCE_CLOSE = ["case-instance", "close"];

    /** fired when a case activity instance is created. */
    public const CASE_ACTIVITY_INSTANCE_CREATE = ["case-activity-instance", "create"];
    /** fired when a case activity instance is updated. */
    public const CASE_ACTIVITY_INSTANCE_UPDATE = ["case-activity-instance", "update"];
    /** fired when a case instance is ended. */
    public const CASE_ACTIVITY_INSTANCE_END = ["case-activity_instance", "end"];

    /**
     * fired when a job is created.
     */
    public const JOB_CREATE = ["job", "create"];

    /**
     * fired when a job is failed.
     */
    public const JOB_FAIL = ["job", "fail"];

    /**
     * fired when a job is succeeded.
     */
    public const JOB_SUCCESS = ["job", "success"];

    /**
     * fired when a job is deleted.
     */
    public const JOB_DELETE = ["job", "delete"];

    /**
     * fired when a decision is evaluated.
     */
    public const DMN_DECISION_EVALUATE = ["decision", "evaluate"];

    /**
     * fired when a batch was started.
     */
    public const BATCH_START = ["batch", "start"];

    /**
     * fired when a batch was completed.
     */
    public const BATCH_END = ["batch", "end"];

    /**
     * fired when an identity link is added
     */
    public const IDENTITY_LINK_ADD = ["identity-link-add", "add-identity-link"];

    /**
     * fired when an identity link is removed
     */
    public const IDENTITY_LINK_DELETE = ["identity-link-delete", "delete-identity-link"];

    /**
     * fired when an external task is created.
     */
    public const EXTERNAL_TASK_CREATE = ["external-task", "create"];

    /**
     * fired when an external task has failed.
     */
    public const EXTERNAL_TASK_FAIL = ["external-task", "fail"];

    /**
     * fired when an external task has succeeded.
     */
    public const EXTERNAL_TASK_SUCCESS = ["external-task", "success"];

    /**
     * fired when an external task is deleted.
     */
    public const EXTERNAL_TASK_DELETE = ["external-task", "delete"];


    /**
     * fired when used operation log is created.
     */
    public const USER_OPERATION_LOG = ["user-operation-log", "create"];

    public function __construct(string $entityType, string $eventName)
    {
        $this->entityType = $entityType;
        $this->eventName = $eventName;
    }

    protected $entityType;
    protected $eventName;

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }
}
