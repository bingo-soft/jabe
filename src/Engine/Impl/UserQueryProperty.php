<?php

namespace Jabe\Engine\Impl;

class UserQueryProperty
{
    private static $USER_ID;
    private static $FIRST_NAME;
    private static $LAST_NAME;
    private static $EMAIL;

    public static function userId(): QueryPropertyImpl
    {
        if (self::$USER_ID === null) {
            self::$USER_ID = new QueryPropertyImpl("ID_");
        }
        return self::$USER_ID;
    }

    public static function firstName(): QueryPropertyImpl
    {
        if (self::$FIRST_NAME === null) {
            self::$FIRST_NAME = new QueryPropertyImpl("FIRST_");
        }
        return self::$FIRST_NAME;
    }

    public static function lastName(): QueryPropertyImpl
    {
        if (self::$LAST_NAME === null) {
            self::$LAST_NAME = new QueryPropertyImpl("LAST_");
        }
        return self::$LAST_NAME;
    }

    public static function email(): QueryPropertyImpl
    {
        if (self::$EMAIL === null) {
            self::$EMAIL = new QueryPropertyImpl("EMAIL_");
        }
        return self::$EMAIL;
    }
}
