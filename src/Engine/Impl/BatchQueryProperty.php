<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Query\QueryPropertyInterface;

class BatchQueryProperty
{
    private static $ID;
    private static $TENANT_ID;

    public static function id(): QueryPropertyInterface
    {
        if (self::$ID == null) {
            self::$ID = new QueryPropertyImpl("ID_");
        }
        return self::$ID;
    }

    public static function tenantId(): QueryPropertyInterface
    {
        if (self::$TENANT_ID == null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
