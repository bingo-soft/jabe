<?php

namespace Jabe\Engine\Impl;

class ProcessInstanceQueryProperty
{
    private static $PROCESS_INSTANCE_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $PROCESS_DEFINITION_ID;
    private static $TENANT_ID;
    private static $BUSINESS_KEY;

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID === null) {
            //@TODO. Same name properties!
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_KEY === null) {
            self::$PROCESS_DEFINITION_KEY = new QueryPropertyImpl("KEY_");
        }
        return self::$PROCESS_DEFINITION_KEY;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID === null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }

    public static function businessKey(): QueryPropertyImpl
    {
        if (self::$BUSINESS_KEY === null) {
            self::$BUSINESS_KEY = new QueryPropertyImpl("BUSINESS_KEY_");
        }
        return self::$BUSINESS_KEY;
    }
}
