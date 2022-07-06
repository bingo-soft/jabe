<?php

namespace Jabe\Engine\Impl;

class JobDefinitionQueryProperty
{
    private static $JOB_DEFINITION_ID;
    private static $ACTIVITY_ID;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $JOB_TYPE;
    private static $JOB_CONFIGURATION;
    private static $TENANT_ID;

    public static function jobDefinitionId(): QueryPropertyImpl
    {
        if (self::$JOB_DEFINITION_ID === null) {
            self::$JOB_DEFINITION_ID = new QueryPropertyImpl("ID_");
        }
        return self::$JOB_DEFINITION_ID;
    }

    public static function activityId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_ID === null) {
            self::$ACTIVITY_ID = new QueryPropertyImpl("ACT_ID_");
        }
        return self::$ACTIVITY_ID;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID === null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("PROC_DEF_ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_KEY === null) {
            self::$PROCESS_DEFINITION_KEY = new QueryPropertyImpl("PROC_DEF_KEY_");
        }
        return self::$PROCESS_DEFINITION_KEY;
    }

    public static function jobType(): QueryPropertyImpl
    {
        if (self::$JOB_TYPE === null) {
            self::$JOB_TYPE = new QueryPropertyImpl("JOB_TYPE_");
        }
        return self::$JOB_TYPE;
    }

    public static function jobConfiguration(): QueryPropertyImpl
    {
        if (self::$JOB_CONFIGURATION === null) {
            self::$JOB_CONFIGURATION = new QueryPropertyImpl("JOB_CONFIGURATION_");
        }
        return self::$JOB_CONFIGURATION;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
