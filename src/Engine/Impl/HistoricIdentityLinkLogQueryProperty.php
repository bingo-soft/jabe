<?php

namespace Jabe\Engine\Impl;

class HistoricIdentityLinkLogQueryProperty
{
    private static $ID;
    private static $TIME;
    private static $TYPE;
    private static $USER_ID;
    private static $GROUP_ID;
    private static $TASK_ID;
    private static $PROC_DEFINITION_ID;
    private static $PROC_DEFINITION_KEY;
    private static $OPERATION_TYPE;
    private static $ASSIGNER_ID;
    private static $TENANT_ID;

    public static function id(): QueryPropertyImpl
    {
        if (self::$ID == null) {
            self::$ID = new QueryPropertyImpl("ID_");
        }
        return self::$ID;
    }

    public static function time(): QueryPropertyImpl
    {
        if (self::$TIME == null) {
            self::$TIME = new QueryPropertyImpl("TIMESTAMP_");
        }
        return self::$TIME;
    }

    public static function type(): QueryPropertyImpl
    {
        if (self::$TYPE == null) {
            self::$TYPE = new QueryPropertyImpl("TYPE_");
        }
        return self::$TYPE;
    }

    public static function userId(): QueryPropertyImpl
    {
        if (self::$USER_ID == null) {
            self::$USER_ID = new QueryPropertyImpl("USER_ID_");
        }
        return self::$USER_ID;
    }

    public static function groupId(): QueryPropertyImpl
    {
        if (self::$GROUP_ID == null) {
            self::$GROUP_ID = new QueryPropertyImpl("GROUP_ID_");
        }
        return self::$GROUP_ID;
    }

    public static function taskId(): QueryPropertyImpl
    {
        if (self::$TASK_ID == null) {
            self::$TASK_ID = new QueryPropertyImpl("TASK_ID_");
        }
        return self::$TASK_ID;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROC_DEFINITION_ID == null) {
            self::$PROC_DEFINITION_ID = new QueryPropertyImpl("PROC_DEF_ID_");
        }
        return self::$PROC_DEFINITION_ID;
    }

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROC_DEFINITION_KEY == null) {
            self::$PROC_DEFINITION_KEY = new QueryPropertyImpl("PROC_DEF_KEY_");
        }
        return self::$PROC_DEFINITION_KEY;
    }

    public static function operationType(): QueryPropertyImpl
    {
        if (self::$OPERATION_TYPE == null) {
            self::$OPERATION_TYPE = new QueryPropertyImpl("OPERATION_TYPE_");
        }
        return self::$OPERATION_TYPE;
    }

    public static function assignerId(): QueryPropertyImpl
    {
        if (self::$ASSIGNER_ID == null) {
            self::$ASSIGNER_ID = new QueryPropertyImpl("ASSIGNER_ID_");
        }
        return self::$ASSIGNER_ID;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID == null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
