<?php

namespace Jabe\Engine\Impl;

class HistoricDetailQueryProperty
{
    private static $PROCESS_INSTANCE_ID;
    private static $VARIABLE_NAME;
    private static $VARIABLE_TYPE;
    private static $VARIABLE_REVISION;
    private static $TIME;
    private static $SEQUENCE_COUNTER;
    private static $TENANT_ID;

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID == null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROC_INST_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function variableName(): QueryPropertyImpl
    {
        if (self::$VARIABLE_NAME == null) {
            self::$VARIABLE_NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$VARIABLE_NAME;
    }

    public static function variableType(): QueryPropertyImpl
    {
        if (self::$VARIABLE_TYPE == null) {
            self::$VARIABLE_TYPE = new QueryPropertyImpl("TYPE_");
        }
        return self::$VARIABLE_TYPE;
    }

    public static function variableRevision(): QueryPropertyImpl
    {
        if (self::$VARIABLE_REVISION == null) {
            self::$VARIABLE_REVISION = new QueryPropertyImpl("REV_");
        }
        return self::$VARIABLE_REVISION;
    }

    public static function time(): QueryPropertyImpl
    {
        if (self::$TIME == null) {
            self::$TIME = new QueryPropertyImpl("TIME_");
        }
        return self::$TIME;
    }

    public static function sequenceCounter(): QueryPropertyImpl
    {
        if (self::$SEQUENCE_COUNTER == null) {
            self::$SEQUENCE_COUNTER = new QueryPropertyImpl("SEQUENCE_COUNTER_");
        }
        return self::$SEQUENCE_COUNTER;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID == null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
