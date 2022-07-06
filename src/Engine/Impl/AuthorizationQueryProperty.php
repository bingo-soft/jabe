<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Query\QueryPropertyInterface;

class AuthorizationQueryProperty
{
    private static $RESOURCE_TYPE;
    private static $RESOURCE_ID;

    public static function resourceType(): QueryPropertyInterface
    {
        if (self::$RESOURCE_TYPE === null) {
            self::$RESOURCE_TYPE = new QueryPropertyImpl("RESOURCE_TYPE_");
        }
        return self::$RESOURCE_TYPE;
    }

    public static function resourceId(): QueryPropertyInterface
    {
        if (self::$RESOURCE_ID === null) {
            self::$RESOURCE_ID = new QueryPropertyImpl("RESOURCE_ID_");
        }
        return self::$RESOURCE_ID;
    }
}
