<?php

namespace Jabe\Impl;

class OperationLogQueryProperty
{
    private static $TIMESTAMP;

    public static function timestamp(): QueryPropertyImpl
    {
        if (self::$TIMESTAMP === null) {
            self::$TIMESTAMP = new QueryPropertyImpl("TIMESTAMP_");
        }
        return self::$TIMESTAMP;
    }
}
