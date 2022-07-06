<?php

namespace Jabe\Engine\Impl;

class HistoricBatchQueryProperty
{
    private static $ID;
    private static $TENANT_ID;
    private static $START_TIME;
    private static $END_TIME;

    public static function id(): QueryPropertyImpl
    {
        if (self::$ID === null) {
            self::$ID = new QueryPropertyImpl("ID_");
        }
        return self::$ID;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
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
}
