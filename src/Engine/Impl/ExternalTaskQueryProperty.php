<?php

namespace Jabe\Engine\Impl;

class ExternalTaskQueryProperty
{
    private static $ID;
    private static $LOCK_EXPIRATION_TIME;
    private static $PROCESS_INSTANCE_ID;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $TENANT_ID;
    private static $PRIORITY;

    public static function id(): QueryPropertyImpl
    {
        if (self::$ID === null) {
            self::$ID = new QueryPropertyImpl("ID_");
        }
        return self::$ID;
    }

    public static function lockExpirationTime(): QueryPropertyImpl
    {
        if (self::$LOCK_EXPIRATION_TIME === null) {
            self::$LOCK_EXPIRATION_TIME = new QueryPropertyImpl("LOCK_EXP_TIME_");
        }
        return self::$LOCK_EXPIRATION_TIME;
    }

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID === null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROC_INST_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
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

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }

    public static function priority(): QueryPropertyImpl
    {
        if (self::$PRIORITY === null) {
            self::$PRIORITY = new QueryPropertyImpl("PRIORITY_");
        }
        return self::$PRIORITY;
    }
}
