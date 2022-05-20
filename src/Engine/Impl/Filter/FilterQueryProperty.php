<?php

namespace Jabe\Engine\Impl\Filter;

class FilterQueryProperty
{
    private static $FILTER_ID;
    private static $RESOURCE_TYPE;
    private static $NAME;
    private static $OWNER;
    private static $QUERY;
    private static $PROPERTIES;

    public static function filterId(): QueryPropertyImpl
    {
        if (self::$FILTER_ID == null) {
            self::$FILTER_ID = new QueryPropertyImpl("ID_");
        }
        return self::$FILTER_ID;
    }

    public static function resourceType(): QueryPropertyImpl
    {
        if (self::$RESOURCE_TYPE == null) {
            self::$RESOURCE_TYPE = new QueryPropertyImpl("RESOURCE_TYPE_");
        }
        return self::$RESOURCE_TYPE;
    }

    public static function name(): QueryPropertyImpl
    {
        if (self::$NAME == null) {
            self::$NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$NAME;
    }

    public static function owner(): QueryPropertyImpl
    {
        if (self::$OWNER == null) {
            self::$OWNER = new QueryPropertyImpl("OWNER_");
        }
        return self::$OWNER;
    }

    public static function query(): QueryPropertyImpl
    {
        if (self::$QUERY == null) {
            self::$QUERY = new QueryPropertyImpl("QUERY_");
        }
        return self::$QUERY;
    }

    public static function properties(): QueryPropertyImpl
    {
        if (self::$PROPERTIES == null) {
            self::$PROPERTIES = new QueryPropertyImpl("PROPERTIES_");
        }
        return self::$PROPERTIES;
    }
}
