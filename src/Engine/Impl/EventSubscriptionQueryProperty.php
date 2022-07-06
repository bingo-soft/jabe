<?php

namespace Jabe\Engine\Impl;

class EventSubscriptionQueryProperty
{
    private static $CREATED;
    private static $TENANT_ID;

    public static function created(): QueryPropertyImpl
    {
        if (self::$CREATED === null) {
            self::$CREATED = new QueryPropertyImpl("CREATED_");
        }
        return self::$CREATED;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
