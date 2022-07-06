<?php

namespace Jabe\Engine\Impl;

class HistoricProcessInstanceQueryProperty
{
    private static $PROCESS_INSTANCE_ID_;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $PROCESS_DEFINITION_NAME;
    private static $PROCESS_DEFINITION_VERSION;
    private static $BUSINESS_KEY;
    private static $START_TIME;
    private static $END_TIME;
    private static $DURATION;
    private static $TENANT_ID;

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID_ === null) {
            self::$PROCESS_INSTANCE_ID_ = new QueryPropertyImpl("PROC_INST_ID_");
        }
        return self::$PROCESS_INSTANCE_ID_;
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

    public static function processDefinitionName(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_NAME === null) {
            self::$PROCESS_DEFINITION_NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$PROCESS_DEFINITION_NAME;
    }

    public static function processDefinitionVersion(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_VERSION === null) {
            self::$PROCESS_DEFINITION_VERSION = new QueryPropertyImpl("VERSION_");
        }
        return self::$PROCESS_DEFINITION_VERSION;
    }

    public static function businessKey(): QueryPropertyImpl
    {
        if (self::$BUSINESS_KEY === null) {
            self::$BUSINESS_KEY = new QueryPropertyImpl("BUSINESS_KEY_");
        }
        return self::$BUSINESS_KEY;
    }

    public static function startTime(): QueryPropertyImpl
    {
        if (self::$START_TIME === null) {
            self::$START_TIME = new QueryPropertyImpl("START_TIME_");
        }
        return self::$START_TIME;
    }

    public static function endTime(): QueryPropertyImpl
    {
        if (self::$END_TIME === null) {
            self::$END_TIME = new QueryPropertyImpl("END_TIME_");
        }
        return self::$END_TIME;
    }

    public static function duration(): QueryPropertyImpl
    {
        if (self::$DURATION === null) {
            self::$DURATION = new QueryPropertyImpl("DURATION_");
        }
        return self::$DURATION;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
