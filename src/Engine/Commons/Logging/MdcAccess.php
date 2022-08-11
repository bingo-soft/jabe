<?php

namespace Jabe\Engine\Commons\Logging;

class MdcAccess
{
    private static $MDC = [];

    public static function remove(string $key): void
    {
        if (array_key_exists($key, self::$MDC)) {
            unset(self::$MDC[$key]);
        }
    }

    public static function get(string $key): ?string
    {
        if (array_key_exists($key, self::$MDC)) {
            return self::$MDC[$key];
        }
        return null;
    }

    public static function put(string $key, string $value): void
    {
        self::$MDC[$key] = $value;
    }
}
