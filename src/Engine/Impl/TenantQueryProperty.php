<?php

namespace Jabe\Engine\Impl;

class TenantQueryProperty
{
    private static $GROUP_ID;
    private static $NAME;

    public static function groupId(): QueryPropertyImpl
    {
        if (self::$GROUP_ID === null) {
            self::$GROUP_ID = new QueryPropertyImpl("ID_");
        }
        return self::$GROUP_ID;
    }

    public static function name(): QueryPropertyImpl
    {
        if (self::$NAME === null) {
            self::$NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$NAME;
    }
}
