<?php

namespace Jabe\Engine\Impl;

class ExecutionQueryProperty
{
    private static $PROCESS_INSTANCE_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $PROCESS_DEFINITION_ID;
    private static $SEQUENCE_COUNTER;
    private static $TENANT_ID;

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID === null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROC_INST_ID_");
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

    public static function sequenceCounter(): QueryPropertyImpl
    {
        if (self::$SEQUENCE_COUNTER === null) {
            self::$SEQUENCE_COUNTER = new QueryPropertyImpl("SEQUENCE_COUNTER_");
        }
        return self::$SEQUENCE_COUNTER;
    }

    public function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
