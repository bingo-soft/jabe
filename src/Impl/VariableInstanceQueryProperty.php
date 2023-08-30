<?php

namespace Jabe\Impl;

class VariableInstanceQueryProperty
{
    private static $VARIABLE_NAME;
    private static $VARIABLE_TYPE;
    private static $ACTIVITY_INSTANCE_ID;
    private static $EXECUTION_ID;
    private static $TASK_ID;
    private static $CASE_EXECUTION_ID;
    private static $CASE_INSTANCE_ID;
    private static $TENANT_ID;

    private static $TEXT;
    private static $TEXT_AS_LOWER;
    private static $DOUBLE;
    private static $LONG;

    public static function variableName(): QueryPropertyImpl
    {
        if (self::$VARIABLE_NAME === null) {
            self::$VARIABLE_NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$VARIABLE_NAME;
    }

    public static function variableType(): QueryPropertyImpl
    {
        if (self::$VARIABLE_TYPE === null) {
            self::$VARIABLE_TYPE = new QueryPropertyImpl("TYPE_");
        }
        return self::$VARIABLE_TYPE;
    }

    public static function activityInstanceId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_INSTANCE_ID === null) {
            self::$ACTIVITY_INSTANCE_ID = new QueryPropertyImpl("ACT_INST_ID_");
        }
        return self::$ACTIVITY_INSTANCE_ID;
    }

    public static function executionId(): QueryPropertyImpl
    {
        if (self::$EXECUTION_ID === null) {
            self::$EXECUTION_ID = new QueryPropertyImpl("EXECUTION_ID_");
        }
        return self::$EXECUTION_ID;
    }

    public static function taskId(): QueryPropertyImpl
    {
        if (self::$TASK_ID === null) {
            self::$TASK_ID = new QueryPropertyImpl("TASK_ID_");
        }
        return self::$TASK_ID;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }

    public static function text(): QueryPropertyImpl
    {
        if (self::$TEXT === null) {
            self::$TEXT = new QueryPropertyImpl("TEXT_");
        }
        return self::$TEXT;
    }

    public static function textAsLower(): QueryPropertyImpl
    {
        if (self::$TEXT_AS_LOWER === null) {
            self::$TEXT_AS_LOWER = new QueryPropertyImpl("TEXT_", "LOWER");
        }
        return self::$TEXT_AS_LOWER;
    }

    public static function double(): QueryPropertyImpl
    {
        if (self::$DOUBLE === null) {
            self::$DOUBLE = new QueryPropertyImpl("DOUBLE_");
        }
        return self::$DOUBLE;
    }

    public static function long(): QueryPropertyImpl
    {
        if (self::$LONG === null) {
            self::$LONG = new QueryPropertyImpl("LONG_");
        }
        return self::$LONG;
    }
}
