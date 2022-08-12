<?php

namespace Jabe\Impl\History\Event;

class HistoryEventTypes implements HistoryEventTypeInterface
{
    /** fired when a process instance is started. */
    private static $PROCESS_INSTANCE_START;
    /** fired when a process instance is updated */
    private static $PROCESS_INSTANCE_UPDATE;
    /** fired when a process instance is migrated */
    private static $PROCESS_INSTANCE_MIGRATE;
    /** fired when a process instance is ended. */
    private static $PROCESS_INSTANCE_END;

    public static function processInstanceStart(): HistoryEventTypeInterface
    {
        if (self::$PROCESS_INSTANCE_START === null) {
            self::$PROCESS_INSTANCE_START = new HistoryEventTypes("process-instance", "start");
        }
        return self::$PROCESS_INSTANCE_START;
    }

    public static function processInstanceUpdate(): HistoryEventTypeInterface
    {
        if (self::$PROCESS_INSTANCE_UPDATE === null) {
            self::$PROCESS_INSTANCE_UPDATE = new HistoryEventTypes("process-instance-update", "update");
        }
        return self::$PROCESS_INSTANCE_UPDATE;
    }

    public static function processInstanceMigrate(): HistoryEventTypeInterface
    {
        if (self::$PROCESS_INSTANCE_MIGRATE === null) {
            self::$PROCESS_INSTANCE_MIGRATE = new HistoryEventTypes("process-instance", "migrate");
        }
        return self::$PROCESS_INSTANCE_MIGRATE;
    }

    public static function processInstanceEnd(): HistoryEventTypeInterface
    {
        if (self::$PROCESS_INSTANCE_END === null) {
            self::$PROCESS_INSTANCE_END = new HistoryEventTypes("process-instance", "end");
        }
        return self::$PROCESS_INSTANCE_END;
    }

    /** fired when an activity instance is started. */
    private static $ACTIVITY_INSTANCE_START;
    /** fired when an activity instance is updated. */
    private static $ACTIVITY_INSTANCE_UPDATE;
    /** fired when an activity instance is migrated. */
    private static $ACTIVITY_INSTANCE_MIGRATE;
    /** fired when an activity instance is ended. */
    private static $ACTIVITY_INSTANCE_END;

    public static function activityInstanceStart(): HistoryEventTypeInterface
    {
        if (self::$ACTIVITY_INSTANCE_START === null) {
            self::$ACTIVITY_INSTANCE_START = new HistoryEventTypes("activity-instance", "start");
        }
        return self::$ACTIVITY_INSTANCE_START;
    }

    public static function activityInstanceUpdate(): HistoryEventTypeInterface
    {
        if (self::$ACTIVITY_INSTANCE_UPDATE === null) {
            self::$ACTIVITY_INSTANCE_UPDATE = new HistoryEventTypes("activity-instance", "update");
        }
        return self::$ACTIVITY_INSTANCE_UPDATE;
    }

    public static function activityInstanceMigrate(): HistoryEventTypeInterface
    {
        if (self::$ACTIVITY_INSTANCE_MIGRATE === null) {
            self::$ACTIVITY_INSTANCE_MIGRATE = new HistoryEventTypes("activity-instance", "migrate");
        }
        return self::$ACTIVITY_INSTANCE_MIGRATE;
    }

    public static function activityInstanceEnd(): HistoryEventTypeInterface
    {
        if (self::$ACTIVITY_INSTANCE_END === null) {
            self::$ACTIVITY_INSTANCE_END = new HistoryEventTypes("activity-instance", "end");
        }
        return self::$ACTIVITY_INSTANCE_END;
    }

    /** fired when a task instance is created. */
    private static $TASK_INSTANCE_CREATE;
    /** fired when a task instance is updated. */
    private static $TASK_INSTANCE_UPDATE;
    /** fired when a task instance is migrated. */
    private static $TASK_INSTANCE_MIGRATE;
    /** fired when a task instance is completed. */
    private static $TASK_INSTANCE_COMPLETE;
    /** fired when a task instance is deleted. */
    private static $TASK_INSTANCE_DELETE;

    public static function taskInstanceCreate(): HistoryEventTypeInterface
    {
        if (self::$TASK_INSTANCE_CREATE === null) {
            self::$TASK_INSTANCE_CREATE = new HistoryEventTypes("task-instance", "create");
        }
        return self::$TASK_INSTANCE_CREATE;
    }

    public static function taskInstanceUpdate(): HistoryEventTypeInterface
    {
        if (self::$TASK_INSTANCE_UPDATE === null) {
            self::$TASK_INSTANCE_UPDATE = new HistoryEventTypes("task-instance", "update");
        }
        return self::$TASK_INSTANCE_UPDATE;
    }

    public static function taskInstanceMigrate(): HistoryEventTypeInterface
    {
        if (self::$TASK_INSTANCE_MIGRATE === null) {
            self::$TASK_INSTANCE_MIGRATE = new HistoryEventTypes("task-instance", "migrate");
        }
        return self::$TASK_INSTANCE_MIGRATE;
    }

    public static function taskInstanceComplete(): HistoryEventTypeInterface
    {
        if (self::$TASK_INSTANCE_COMPLETE === null) {
            self::$TASK_INSTANCE_COMPLETE = new HistoryEventTypes("task-instance", "complete");
        }
        return self::$TASK_INSTANCE_COMPLETE;
    }

    public static function taskInstanceDelete(): HistoryEventTypeInterface
    {
        if (self::$TASK_INSTANCE_DELETE === null) {
            self::$TASK_INSTANCE_DELETE = new HistoryEventTypes("task-instance", "delete");
        }
        return self::$TASK_INSTANCE_DELETE;
    }

    /** fired when a variable instance is created. */
    private static $VARIABLE_INSTANCE_CREATE;
    /** fired when a variable instance is updated. */
    private static $VARIABLE_INSTANCE_UPDATE;
    /** fired when a variable instance is migrated. */
    private static $VARIABLE_INSTANCE_MIGRATE;
    /** fired when a variable instance is updated. */
    private static $VARIABLE_INSTANCE_UPDATE_DETAIL;
    /** fired when a variable instance is deleted. */
    private static $VARIABLE_INSTANCE_DELETE;

    public static function variableInstanceCreate(): HistoryEventTypeInterface
    {
        if (self::$VARIABLE_INSTANCE_CREATE === null) {
            self::$VARIABLE_INSTANCE_CREATE = new HistoryEventTypes("variable-instance", "create");
        }
        return self::$VARIABLE_INSTANCE_CREATE;
    }

    public static function variableInstanceUpdate(): HistoryEventTypeInterface
    {
        if (self::$VARIABLE_INSTANCE_UPDATE === null) {
            self::$VARIABLE_INSTANCE_UPDATE = new HistoryEventTypes("variable-instance", "update");
        }
        return self::$VARIABLE_INSTANCE_UPDATE;
    }

    public static function variableInstanceMigrate(): HistoryEventTypeInterface
    {
        if (self::$VARIABLE_INSTANCE_MIGRATE === null) {
            self::$VARIABLE_INSTANCE_MIGRATE = new HistoryEventTypes("variable-instance", "migrate");
        }
        return self::$VARIABLE_INSTANCE_MIGRATE;
    }

    public static function variableInstanceUpdateDetail(): HistoryEventTypeInterface
    {
        if (self::$VARIABLE_INSTANCE_UPDATE_DETAIL === null) {
            self::$VARIABLE_INSTANCE_UPDATE_DETAIL = new HistoryEventTypes("variable-instance", "update-detail");
        }
        return self::$VARIABLE_INSTANCE_UPDATE_DETAIL;
    }

    public static function variableInstanceDelete(): HistoryEventTypeInterface
    {
        if (self::$VARIABLE_INSTANCE_DELETE === null) {
            self::$VARIABLE_INSTANCE_DELETE = new HistoryEventTypes("variable-instance", "delete");
        }
        return self::$VARIABLE_INSTANCE_DELETE;
    }

    /** fired when a form property is updated. */
    private static $FORM_PROPERTY_UPDATE;

    public static function formPropertyUpdate(): HistoryEventTypeInterface
    {
        if (self::$FORM_PROPERTY_UPDATE === null) {
            self::$FORM_PROPERTY_UPDATE = new HistoryEventTypes("form-property", "form-property-update");
        }
        return self::$FORM_PROPERTY_UPDATE;
    }

    /** fired when an incident is created. */
    private static $INCIDENT_CREATE;
    /** fired when an incident is migrated. */
    private static $INCIDENT_MIGRATE;
    /** fired when an incident is deleted. */
    private static $INCIDENT_DELETE;
    /** fired when an incident is resolved. */
    private static $INCIDENT_RESOLVE;
    /** fired when an incident is updated. */
    private static $INCIDENT_UPDATE;

    public static function incidentCreate(): HistoryEventTypeInterface
    {
        if (self::$INCIDENT_CREATE === null) {
            self::$INCIDENT_CREATE = new HistoryEventTypes("incident", "create");
        }
        return self::$INCIDENT_CREATE;
    }

    public static function incidentMigrate(): HistoryEventTypeInterface
    {
        if (self::$INCIDENT_MIGRATE === null) {
            self::$INCIDENT_MIGRATE = new HistoryEventTypes("incident", "migrate");
        }
        return self::$INCIDENT_MIGRATE;
    }

    public static function incidentDelete(): HistoryEventTypeInterface
    {
        if (self::$INCIDENT_DELETE === null) {
            self::$INCIDENT_DELETE = new HistoryEventTypes("incident", "delete");
        }
        return self::$INCIDENT_DELETE;
    }

    public static function incidentResolve(): HistoryEventTypeInterface
    {
        if (self::$INCIDENT_RESOLVE === null) {
            self::$INCIDENT_RESOLVE = new HistoryEventTypes("incident", "resolve");
        }
        return self::$INCIDENT_RESOLVE;
    }

    public static function incidentUpdate(): HistoryEventTypeInterface
    {
        if (self::$INCIDENT_UPDATE === null) {
            self::$INCIDENT_UPDATE = new HistoryEventTypes("incident", "update");
        }
        return self::$INCIDENT_UPDATE;
    }

    /** fired when a case instance is created. */
    private static $CASE_INSTANCE_CREATE;
    /** fired when a case instance is updated. */
    private static $CASE_INSTANCE_UPDATE;
    /** fired when a case instance is closed. */
    private static $CASE_INSTANCE_CLOSE;
    /** fired when a case activity instance is created. */
    private static $CASE_ACTIVITY_INSTANCE_CREATE;
    /** fired when a case activity instance is updated. */
    private static $CASE_ACTIVITY_INSTANCE_UPDATE;
    /** fired when a case instance is ended. */
    private static $CASE_ACTIVITY_INSTANCE_END;

    public static function caseInstanceCreate(): HistoryEventTypeInterface
    {
        if (self::$CASE_INSTANCE_CREATE === null) {
            self::$CASE_INSTANCE_CREATE = new HistoryEventTypes("case-instance", "create");
        }
        return self::$CASE_INSTANCE_CREATE;
    }

    public static function caseInstanceUpdate(): HistoryEventTypeInterface
    {
        if (self::$CASE_INSTANCE_UPDATE === null) {
            self::$CASE_INSTANCE_UPDATE = new HistoryEventTypes("case-instance", "update");
        }
        return self::$CASE_INSTANCE_UPDATE;
    }

    public static function caseInstanceClose(): HistoryEventTypeInterface
    {
        if (self::$CASE_INSTANCE_CLOSE === null) {
            self::$CASE_INSTANCE_CLOSE = new HistoryEventTypes("case-instance", "close");
        }
        return self::$CASE_INSTANCE_CLOSE;
    }

    public static function caseActivityInstanceCreate(): HistoryEventTypeInterface
    {
        if (self::$CASE_ACTIVITY_INSTANCE_CREATE === null) {
            self::$CASE_ACTIVITY_INSTANCE_CREATE = new HistoryEventTypes("case-activity-instance", "create");
        }
        return self::$CASE_ACTIVITY_INSTANCE_CREATE;
    }

    public static function caseActivityInstanceUpdate(): HistoryEventTypeInterface
    {
        if (self::$CASE_ACTIVITY_INSTANCE_UPDATE === null) {
            self::$CASE_ACTIVITY_INSTANCE_UPDATE = new HistoryEventTypes("case-activity-instance", "update");
        }
        return self::$CASE_ACTIVITY_INSTANCE_UPDATE;
    }

    public static function caseActivityInstanceEnd(): HistoryEventTypeInterface
    {
        if (self::$CASE_ACTIVITY_INSTANCE_END === null) {
            self::$CASE_ACTIVITY_INSTANCE_END = new HistoryEventTypes("case-activity-instance", "end");
        }
        return self::$CASE_ACTIVITY_INSTANCE_END;
    }

    /**
     * fired when a job is created.
     */
    private static $JOB_CREATE;
    /**
     * fired when a job is failed.
     */
    private static $JOB_FAIL;
    /**
     * fired when a job is succeeded.
     */
    private static $JOB_SUCCESS;
    /**
     * fired when a job is deleted.
     */
    private static $JOB_DELETE;

    public static function jobCreate(): HistoryEventTypeInterface
    {
        if (self::$JOB_CREATE === null) {
            self::$JOB_CREATE = new HistoryEventTypes("job", "create");
        }
        return self::$JOB_CREATE;
    }

    public static function jobFail(): HistoryEventTypeInterface
    {
        if (self::$JOB_FAIL === null) {
            self::$JOB_FAIL = new HistoryEventTypes("job", "fail");
        }
        return self::$JOB_FAIL;
    }

    public static function jobSuccess(): HistoryEventTypeInterface
    {
        if (self::$JOB_SUCCESS === null) {
            self::$JOB_SUCCESS = new HistoryEventTypes("job", "success");
        }
        return self::$JOB_SUCCESS;
    }

    public static function jobDelete(): HistoryEventTypeInterface
    {
        if (self::$JOB_DELETE === null) {
            self::$JOB_DELETE = new HistoryEventTypes("job", "delete");
        }
        return self::$JOB_DELETE;
    }

    /**
     * fired when a decision is evaluated.
     */
    private static $DMN_DECISION_EVALUATE;

    public static function dmnDecisionEvaluate(): HistoryEventTypeInterface
    {
        if (self::$DMN_DECISION_EVALUATE === null) {
            self::$DMN_DECISION_EVALUATE = new HistoryEventTypes("decision", "evaluate");
        }
        return self::$DMN_DECISION_EVALUATE;
    }

    /**
     * fired when a batch was started.
     */
    private static $BATCH_START;
    /**
     * fired when a batch was completed.
     */
    private static $BATCH_END;

    public static function batchStart(): HistoryEventTypeInterface
    {
        if (self::$BATCH_START === null) {
            self::$BATCH_START = new HistoryEventTypes("batch", "start");
        }
        return self::$BATCH_START;
    }

    public static function batchEnd(): HistoryEventTypeInterface
    {
        if (self::$BATCH_END === null) {
            self::$BATCH_END = new HistoryEventTypes("batch", "end");
        }
        return self::$BATCH_END;
    }

    /**
     * fired when an identity link is added
     */
    private static $IDENTITY_LINK_ADD;
    /**
     * fired when an identity link is removed
     */
    private static $IDENTITY_LINK_DELETE;

    public static function identityLinkAdd(): HistoryEventTypeInterface
    {
        if (self::$IDENTITY_LINK_ADD === null) {
            self::$IDENTITY_LINK_ADD = new HistoryEventTypes("identity-link-add", "add-identity-link");
        }
        return self::$IDENTITY_LINK_ADD;
    }

    public static function identityLinkDelete(): HistoryEventTypeInterface
    {
        if (self::$IDENTITY_LINK_DELETE === null) {
            self::$IDENTITY_LINK_DELETE = new HistoryEventTypes("identity-link-delete", "delete-identity-link");
        }
        return self::$IDENTITY_LINK_DELETE;
    }

    /**
     * fired when an external task is created.
     */
    private static $EXTERNAL_TASK_CREATE;
    /**
     * fired when an external task has failed.
     */
    private static $EXTERNAL_TASK_FAIL;
    /**
     * fired when an external task has succeeded.
     */
    private static $EXTERNAL_TASK_SUCCESS;
    /**
     * fired when an external task is deleted.
     */
    private static $EXTERNAL_TASK_DELETE;

    public static function externalTaskCreate(): HistoryEventTypeInterface
    {
        if (self::$EXTERNAL_TASK_CREATE === null) {
            self::$EXTERNAL_TASK_CREATE = new HistoryEventTypes("external-task", "create");
        }
        return self::$EXTERNAL_TASK_CREATE;
    }

    public static function externalTaskFail(): HistoryEventTypeInterface
    {
        if (self::$EXTERNAL_TASK_FAIL === null) {
            self::$EXTERNAL_TASK_FAIL = new HistoryEventTypes("external-task", "fail");
        }
        return self::$EXTERNAL_TASK_FAIL;
    }

    public static function externalTaskSuccess(): HistoryEventTypeInterface
    {
        if (self::$EXTERNAL_TASK_SUCCESS === null) {
            self::$EXTERNAL_TASK_SUCCESS = new HistoryEventTypes("external-task", "success");
        }
        return self::$EXTERNAL_TASK_SUCCESS;
    }

    public static function externalTaskDelete(): HistoryEventTypeInterface
    {
        if (self::$EXTERNAL_TASK_DELETE === null) {
            self::$EXTERNAL_TASK_DELETE = new HistoryEventTypes("external-task", "delete");
        }
        return self::$EXTERNAL_TASK_DELETE;
    }

    /**
     * fired when used operation log is created.
     */
    private static $USER_OPERATION_LOG;

    public static function userOperationLog(): HistoryEventTypeInterface
    {
        if (self::$USER_OPERATION_LOG === null) {
            self::$USER_OPERATION_LOG = new HistoryEventTypes("user-operation-log", "create");
        }
        return self::$USER_OPERATION_LOG;
    }

    private function __construct(string $entityType, string $eventName)
    {
        $this->entityType = $entityType;
        $this->eventName = $eventName;
    }

    protected $entityType;
    protected $eventName;

    public function equals(HistoryEventTypeInterface $obj): bool
    {
        return $this->eventName == $obj->getEventName() && $this->entityType == $obj->getEntityType();
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }
}
