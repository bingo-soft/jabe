<?php

namespace Jabe\Engine\Impl;

class HistoricTaskInstanceQueryProperty
{
    private static $HISTORIC_TASK_INSTANCE_ID;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_INSTANCE_ID;
    private static $EXECUTION_ID;
    private static $ACTIVITY_INSTANCE_ID;
    private static $TASK_NAME;
    private static $TASK_DESCRIPTION;
    private static $TASK_ASSIGNEE;
    private static $TASK_OWNER;
    private static $TASK_DEFINITION_KEY;
    private static $DELETE_REASON;
    private static $START;
    private static $END;
    private static $DURATION;
    private static $TASK_PRIORITY;
    private static $TASK_DUE_DATE;
    private static $TASK_FOLLOW_UP_DATE;// QueryPropertyImpl("FOLLOW_UP_DATE_");
    //private static $CASE_DEFINITION_ID;// QueryPropertyImpl("CASE_DEFINITION_ID_");
    //private static $CASE_INSTANCE_ID;// QueryPropertyImpl("CASE_INSTANCE_ID_");
    //private static $CASE_EXECUTION_ID;// QueryPropertyImpl("CASE_EXECUTION_ID_");
    private static $TENANT_ID;

    public static function historicTaskInstanceId(): QueryPropertyImpl
    {
        if (self::$HISTORIC_TASK_INSTANCE_ID === null) {
            self::$HISTORIC_TASK_INSTANCE_ID = new QueryPropertyImpl("ID_");
        }
        return self::$HISTORIC_TASK_INSTANCE_ID;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID === null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("PROC_DEF_ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID === null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROC_INST_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function executionId(): QueryPropertyImpl
    {
        if (self::$EXECUTION_ID === null) {
            self::$EXECUTION_ID = new QueryPropertyImpl("EXECUTION_ID_");
        }
        return self::$EXECUTION_ID;
    }

    public static function activityInstanceId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_INSTANCE_ID === null) {
            self::$ACTIVITY_INSTANCE_ID = new QueryPropertyImpl("ACT_INST_ID_");
        }
        return self::$ACTIVITY_INSTANCE_ID;
    }

    public static function taskName(): QueryPropertyImpl
    {
        if (self::$TASK_NAME === null) {
            self::$TASK_NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$TASK_NAME;
    }

    public static function taskDescription(): QueryPropertyImpl
    {
        if (self::$TASK_DESCRIPTION === null) {
            self::$TASK_DESCRIPTION = new QueryPropertyImpl("DESCRIPTION_");
        }
        return self::$TASK_DESCRIPTION;
    }

    public static function taskAssignee(): QueryPropertyImpl
    {
        if (self::$TASK_ASSIGNEE === null) {
            self::$TASK_ASSIGNEE = new QueryPropertyImpl("ASSIGNEE_");
        }
        return self::$TASK_ASSIGNEE;
    }

    public static function taskOwner(): QueryPropertyImpl
    {
        if (self::$TASK_OWNER === null) {
            self::$TASK_OWNER = new QueryPropertyImpl("OWNER_");
        }
        return self::$TASK_OWNER;
    }

    public static function taskDefinitionKey(): QueryPropertyImpl
    {
        if (self::$TASK_DEFINITION_KEY === null) {
            self::$TASK_DEFINITION_KEY = new QueryPropertyImpl("TASK_DEF_KEY_");
        }
        return self::$TASK_DEFINITION_KEY;
    }

    public static function deleteReason(): QueryPropertyImpl
    {
        if (self::$DELETE_REASON === null) {
            self::$DELETE_REASON = new QueryPropertyImpl("DELETE_REASON_");
        }
        return self::$DELETE_REASON;
    }

    public static function start(): QueryPropertyImpl
    {
        if (self::$START === null) {
            self::$START = new QueryPropertyImpl("START_TIME_");
        }
        return self::$START;
    }

    public static function end(): QueryPropertyImpl
    {
        if (self::$END === null) {
            self::$END = new QueryPropertyImpl("END_TIME_");
        }
        return self::$END;
    }

    public static function duration(): QueryPropertyImpl
    {
        if (self::$DURATION === null) {
            self::$DURATION = new QueryPropertyImpl("DURATION_");
        }
        return self::$DURATION;
    }

    public static function taskPriority(): QueryPropertyImpl
    {
        if (self::$TASK_PRIORITY === null) {
            self::$TASK_PRIORITY = new QueryPropertyImpl("PRIORITY_");
        }
        return self::$TASK_PRIORITY;
    }

    public static function taskDueDate(): QueryPropertyImpl
    {
        if (self::$TASK_DUE_DATE === null) {
            self::$TASK_DUE_DATE = new QueryPropertyImpl("DUE_DATE_");
        }
        return self::$TASK_DUE_DATE;
    }

    public static function taskFollowUpDate(): QueryPropertyImpl
    {
        if (self::$TASK_FOLLOW_UP_DATE === null) {
            self::$TASK_FOLLOW_UP_DATE = new QueryPropertyImpl("FOLLOW_UP_DATE_");
        }
        return self::$TASK_FOLLOW_UP_DATE;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
