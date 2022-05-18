<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

class TimeUnit
{
    public const DAYS = 'days';
    public const HOURS = 'hours';
    public const MICROSECONDS = 'microseconds';
    public const MILLISECONDS = 'milliseconds';
    public const MINUTES = 'minutes';
    public const NANOSECONDS = 'nanoseconds';
    public const SECONDS = 'seconds';

    public static function toNanos(int $duration, string $units): int
    {
        if (strtolower($units) == self::DAYS) {
            return $duration * 86400000000000;
        }
        if (strtolower($units) == self::HOURS) {
            return $duration * 3600000000000;
        }
        if (strtolower($units) == self::MICROSECONDS) {
            return $duration * 1000;
        }
        if (strtolower($units) == self::MILLISECONDS) {
            return $duration * 1000000;
        }
        if (strtolower($units) == self::MINUTES) {
            return $duration * 60000000000;
        }
        if (strtolower($units) == self::NANOSECONDS) {
            return $duration;
        }
        if (strtolower($units) == self::SECONDS) {
            return $duration * 1000000000;
        }
    }
}
