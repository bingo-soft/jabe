<?php

namespace Jabe\Engine\Impl;

class HistoricJobLogQueryProperty
{
    private static $JOB_ID;
    private static $JOB_DEFINITION_ID;
    private static $TIMESTAMP;
    private static $ACTIVITY_ID;
    private static $EXECUTION_ID;
    private static $PROCESS_INSTANCE_ID;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $DEPLOYMENT_ID;
    private static $DUEDATE;
    private static $RETRIES;
    private static $PRIORITY;
    private static $SEQUENCE_COUNTER;
    private static $TENANT_ID;
    private static $HOSTNAME;

    public static function jobId(): QueryPropertyImpl
    {
        if (self::$JOB_ID === null) {
            self::$JOB_ID = new QueryPropertyImpl("JOB_ID_");
        }
        return self::$JOB_ID;
    }

    public static function jobDefinitionId(): QueryPropertyImpl
    {
        if (self::$JOB_DEFINITION_ID === null) {
            self::$JOB_DEFINITION_ID = new QueryPropertyImpl("JOB_DEF_ID_");
        }
        return self::$JOB_DEFINITION_ID;
    }

    public static function timestamp(): QueryPropertyImpl
    {
        if (self::$TIMESTAMP === null) {
            self::$TIMESTAMP = new QueryPropertyImpl("TIMESTAMP_");
        }
        return self::$TIMESTAMP;
    }

    public static function activityId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_ID === null) {
            self::$ACTIVITY_ID = new QueryPropertyImpl("ACT_ID_");
        }
        return self::$ACTIVITY_ID;
    }

    public static function executionId(): QueryPropertyImpl
    {
        if (self::$EXECUTION_ID === null) {
            self::$EXECUTION_ID = new QueryPropertyImpl("EXECUTION_ID_");
        }
        return self::$EXECUTION_ID;
    }

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID === null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROCESS_INSTANCE_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID === null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("PROCESS_DEF_ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_KEY === null) {
            self::$PROCESS_DEFINITION_KEY = new QueryPropertyImpl("PROCESS_DEF_KEY_");
        }
        return self::$PROCESS_DEFINITION_KEY;
    }

    public static function deploymentId(): QueryPropertyImpl
    {
        if (self::$DEPLOYMENT_ID === null) {
            self::$DEPLOYMENT_ID = new QueryPropertyImpl("DEPLOYMENT_ID_");
        }
        return self::$DEPLOYMENT_ID;
    }

    public static function duedate(): QueryPropertyImpl
    {
        if (self::$DUEDATE === null) {
            self::$DUEDATE = new QueryPropertyImpl("JOB_DUEDATE_");
        }
        return self::$DUEDATE;
    }

    public static function retries(): QueryPropertyImpl
    {
        if (self::$RETRIES === null) {
            self::$RETRIES = new QueryPropertyImpl("JOB_RETRIES_");
        }
        return self::$RETRIES;
    }

    public static function priority(): QueryPropertyImpl
    {
        if (self::$PRIORITY === null) {
            self::$PRIORITY = new QueryPropertyImpl("JOB_PRIORITY_");
        }
        return self::$PRIORITY;
    }

    public static function sequenceCounter(): QueryPropertyImpl
    {
        if (self::$SEQUENCE_COUNTER === null) {
            self::$SEQUENCE_COUNTER = new QueryPropertyImpl("SEQUENCE_COUNTER_");
        }
        return self::$SEQUENCE_COUNTER;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }

    public static function hostname(): QueryPropertyImpl
    {
        if (self::$HOSTNAME === null) {
            self::$HOSTNAME = new QueryPropertyImpl("HOSTNAME_");
        }
        return self::$HOSTNAME;
    }
}
