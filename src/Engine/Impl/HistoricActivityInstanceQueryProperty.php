<?php

namespace Jabe\Engine\Impl;

class HistoricActivityInstanceQueryProperty
{
    private static $HISTORIC_ACTIVITY_INSTANCE_ID;
    private static $PROCESS_INSTANCE_ID;
    private static $EXECUTION_ID;
    private static $ACTIVITY_ID;
    private static $ACTIVITY_NAME;
    private static $ACTIVITY_TYPE;
    private static $PROCESS_DEFINITION_ID;
    private static $START;
    private static $END;
    private static $DURATION;
    private static $SEQUENCE_COUNTER;
    private static $TENANT_ID;

    public static function historicActivityInstanceId(): QueryPropertyImpl
    {
        if (self::$HISTORIC_ACTIVITY_INSTANCE_ID === null) {
            self::$HISTORIC_ACTIVITY_INSTANCE_ID = new QueryPropertyImpl("ID_");
        }
        return self::$HISTORIC_ACTIVITY_INSTANCE_ID;
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

    public static function activityId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_ID === null) {
            self::$ACTIVITY_ID = new QueryPropertyImpl("ACT_ID_");
        }
        return self::$ACTIVITY_ID;
    }

    public static function activityName(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_NAME === null) {
            self::$ACTIVITY_NAME = new QueryPropertyImpl("ACT_NAME_");
        }
        return self::$ACTIVITY_NAME;
    }

    public static function activityType(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_TYPE === null) {
            self::$ACTIVITY_TYPE = new QueryPropertyImpl("ACT_TYPE_");
        }
        return self::$ACTIVITY_TYPE;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID === null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("PROC_DEF_ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
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
}
