<?php

namespace Jabe\Impl;

class HistoricVariableInstanceQueryProperty
{
    private static $PROCESS_INSTANCE_ID;
    private static $VARIABLE_NAME;
    private static $TENANT_ID;

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID === null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROC_INST_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function variableName(): QueryPropertyImpl
    {
        if (self::$VARIABLE_NAME === null) {
            self::$VARIABLE_NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$VARIABLE_NAME;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
