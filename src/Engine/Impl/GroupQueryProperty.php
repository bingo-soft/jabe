<?php

namespace Jabe\Engine\Impl;

class GroupQueryProperty
{
    private static $GROUP_ID;
    private static $NAME;
    private static $TYPE;

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

    public static function type(): QueryPropertyImpl
    {
        if (self::$TYPE === null) {
            self::$TYPE = new QueryPropertyImpl("TYPE_");
        }
        return self::$TYPE;
    }
}
