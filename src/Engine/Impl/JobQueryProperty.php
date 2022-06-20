<?php

namespace Jabe\Engine\Impl;

class JobQueryProperty
{
    private static $JOB_ID;
    private static $PROCESS_INSTANCE_ID;
    private static $EXECUTION_ID;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $DUEDATE;
    private static $RETRIES;
    private static $TYPE;
    private static $PRIORITY;
    private static $TENANT_ID;

    public static function jobId(): QueryPropertyImpl
    {
        if (self::$JOB_ID == null) {
            self::$JOB_ID = new QueryPropertyImpl("ID_");
        }
        return self::$JOB_ID;
    }

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID == null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROCESS_INSTANCE_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function executionId(): QueryPropertyImpl
    {
        if (self::$EXECUTION_ID == null) {
            self::$EXECUTION_ID = new QueryPropertyImpl("EXECUTION_ID_");
        }
        return self::$EXECUTION_ID;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID == null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("PROCESS_DEF_ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_KEY == null) {
            self::$PROCESS_DEFINITION_KEY = new QueryPropertyImpl("PROCESS_DEF_KEY_");
        }
        return self::$PROCESS_DEFINITION_KEY;
    }

    public static function duedate(): QueryPropertyImpl
    {
        if (self::$DUEDATE == null) {
            self::$DUEDATE = new QueryPropertyImpl("DUEDATE_");
        }
        return self::$DUEDATE;
    }

    public static function retries(): QueryPropertyImpl
    {
        if (self::$RETRIES == null) {
            self::$RETRIES = new QueryPropertyImpl("RETRIES_");
        }
        return self::$RETRIES;
    }

    public static function type(): QueryPropertyImpl
    {
        if (self::$TYPE == null) {
            self::$TYPE = new QueryPropertyImpl("TYPE_");
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
