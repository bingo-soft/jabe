<?php

namespace Jabe\Engine\Impl;

class TaskQueryProperty
{
    private static $TASK_ID;// = new QueryPropertyImpl("ID_");
    private static $NAME;// = new QueryPropertyImpl("NAME_");
    private static $NAME_CASE_INSENSITIVE;// = new QueryPropertyImpl("NAME_", "LOWER");
    private static $DESCRIPTION;// = new QueryPropertyImpl("DESCRIPTION_");
    private static $PRIORITY;// = new QueryPropertyImpl("PRIORITY_");
    private static $ASSIGNEE;// = new QueryPropertyImpl("ASSIGNEE_");
    private static $CREATE_TIME;// = new QueryPropertyImpl("CREATE_TIME_");
    private static $UPDATED_AFTER;// = new QueryPropertyImpl("LAST_UPDATED_");
    private static $PROCESS_INSTANCE_ID;// = new QueryPropertyImpl("PROC_INST_ID_");
    private static $CASE_INSTANCE_ID;// = new QueryPropertyImpl("CASE_INST_ID_");
    private static $EXECUTION_ID;// = new QueryPropertyImpl("EXECUTION_ID_");
    private static $CASE_EXECUTION_ID;// = new QueryPropertyImpl("CASE_EXECUTION_ID_");
    private static $DUE_DATE;// = new QueryPropertyImpl("DUE_DATE_");
    private static $FOLLOW_UP_DATE;// = new QueryPropertyImpl("FOLLOW_UP_DATE_");
    private static $TENANT_ID;// = new QueryPropertyImpl("TENANT_ID_");

    public static function taskId(): QueryPropertyImpl
    {
        if (self::$TASK_ID === null) {
            self::$TASK_ID = new QueryPropertyImpl("ID_");
        }
        return self::$TASK_ID;
    }

    public static function name(): QueryPropertyImpl
    {
        if (self::$NAME === null) {
            self::$NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$NAME;
    }

    public static function nameCaseInsesitive(): QueryPropertyImpl
    {
        if (self::$NAME_CASE_INSENSITIVE === null) {
            self::$NAME_CASE_INSENSITIVE = new QueryPropertyImpl("NAME_LOWER");
        }
        return self::$NAME_CASE_INSENSITIVE;
    }

    public static function description(): QueryPropertyImpl
    {
        if (self::$DESCRIPTION === null) {
            self::$DESCRIPTION = new QueryPropertyImpl("DESCRIPTION_");
        }
        return self::$DESCRIPTION;
    }

    public static function priority(): QueryPropertyImpl
    {
        if (self::$PRIORITY === null) {
            self::$PRIORITY = new QueryPropertyImpl("PRIORITY_");
        }
        return self::$PRIORITY;
    }

    public static function assignee(): QueryPropertyImpl
    {
        if (self::$ASSIGNEE === null) {
            self::$ASSIGNEE = new QueryPropertyImpl("ASSIGNEE_");
        }
        return self::$ASSIGNEE;
    }

    public static function createTime(): QueryPropertyImpl
    {
        if (self::$CREATE_TIME === null) {
            self::$CREATE_TIME = new QueryPropertyImpl("CREATE_TIME_");
        }
        return self::$CREATE_TIME;
    }

    public static function updatedAfter(): QueryPropertyImpl
    {
        if (self::$UPDATED_AFTER === null) {
            self::$UPDATED_AFTER = new QueryPropertyImpl("LAST_UPDATED_");
        }
        return self::$UPDATED_AFTER;
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

    public static function dueDate(): QueryPropertyImpl
    {
        if (self::$DUE_DATE === null) {
            self::$DUE_DATE = new QueryPropertyImpl("DUE_DATE_");
        }
        return self::$DUE_DATE;
    }

    public static function followUpDate(): QueryPropertyImpl
    {
        if (self::$FOLLOW_UP_DATE === null) {
            self::$FOLLOW_UP_DATE = new QueryPropertyImpl("FOLLOW_UP_DATE_");
        }
        return self::$FOLLOW_UP_DATE;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
