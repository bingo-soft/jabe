<?php

namespace Jabe\Engine\Impl;

class HistoricExternalTaskLogQueryProperty
{
    private static $EXTERNAL_TASK_ID;
    private static $TIMESTAMP;
    private static $TOPIC_NAME;
    private static $WORKER_ID;
    private static $ACTIVITY_ID;
    private static $ACTIVITY_INSTANCE_ID;
    private static $EXECUTION_ID;
    private static $PROCESS_INSTANCE_ID;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $RETRIES;
    private static $PRIORITY;
    private static $TENANT_ID;

    public static function externalTaskId(): QueryPropertyImpl
    {
        if (self::$EXTERNAL_TASK_ID == null) {
            self::$EXTERNAL_TASK_ID = new QueryPropertyImpl("EXT_TASK_ID_");
        }
        return self::$EXTERNAL_TASK_ID;
    }

    public static function timestamp(): QueryPropertyImpl
    {
        if (self::$TIMESTAMP == null) {
            self::$TIMESTAMP = new QueryPropertyImpl("TIMESTAMP_");
        }
        return self::$TIMESTAMP;
    }

    public static function topicName(): QueryPropertyImpl
    {
        if (self::$TOPIC_NAME == null) {
            self::$TOPIC_NAME = new QueryPropertyImpl("TOPIC_NAME_");
        }
        return self::$TOPIC_NAME;
    }

    public static function workerId(): QueryPropertyImpl
    {
        if (self::$WORKER_ID == null) {
            self::$WORKER_ID = new QueryPropertyImpl("WORKER_ID_");
        }
        return self::$WORKER_ID;
    }

    public static function activityId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_ID == null) {
            self::$ACTIVITY_ID = new QueryPropertyImpl("ACT_ID_");
        }
        return self::$ACTIVITY_ID;
    }

    public static function activityInstanceId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_INSTANCE_ID == null) {
            self::$ACTIVITY_INSTANCE_ID = new QueryPropertyImpl("ACT_INST_ID_");
        }
        return self::$ACTIVITY_INSTANCE_ID;
    }

    public static function executionId(): QueryPropertyImpl
    {
        if (self::$EXECUTION_ID == null) {
            self::$EXECUTION_ID = new QueryPropertyImpl("EXECUTION_ID_");
        }
        return self::$EXECUTION_ID;
    }

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID == null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROC_INST_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID == null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("PROC_DEF_ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_KEY == null) {
            self::$PROCESS_DEFINITION_KEY = new QueryPropertyImpl("PROC_DEF_KEY_");
        }
        return self::$PROCESS_DEFINITION_KEY;
    }

    public static function retries(): QueryPropertyImpl
    {
        if (self::$RETRIES == null) {
            self::$RETRIES = new QueryPropertyImpl("RETRIES_");
        }
        return self::$RETRIES;
    }

    public static function priority(): QueryPropertyImpl
    {
        if (self::$PRIORITY == null) {
            self::$PRIORITY = new QueryPropertyImpl("PRIORITY_");
        }
        return self::$PRIORITY;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID == null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
