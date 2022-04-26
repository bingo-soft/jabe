<?php

namespace Jabe\Engine\Impl\Util;

class ClockUtil
{
    private static $CURRENT_TIME = 'NOW';

    private static $OFFSET_IN_MILLIS = 0;

    public static function setCurrentTime($currentTime): void
    {
        if ($currentTime instanceof \DateTime) {
            $currentTime = $currentTime->format('c');
        }
        self::$CURRENT_TIME = $currentTime;
    }

    public static function reset(): void
    {
        self::resetClock();
    }

    public static function getCurrentTime(): \DateTime
    {
        return self::now();
    }

    public static function now(): \DateTime
    {
        return self::getCurrentTimeWithOffset();
    }

    private static function getCurrentTimeWithOffset(): \DateTime
    {
        if (self::$OFFSET_IN_MILLIS == 0) {
            return new \DateTime(self::$CURRENT_TIME);
        }

        $dt = new \DateTime(self::$CURRENT_TIME);
        $dt->modify('+ ' . self::$OFFSET_IN_MILLIS . ' milliseconds');
        return $dt;
    }

    /**
     * Moves the clock by the given offset and keeps it running from that point
     * on.
     *
     * @param offsetInMillis
     *          the offset to move the clock by
     * @return the new 'now'
     */
    public static function offset(int $offsetInMillis): \DateTime
    {
        self::$OFFSET_IN_MILLIS = $offsetInMillis;
        return self::now();
    }

    public static function resetClock(): \DateTime
    {
        self::$CURRENT_TIME = 'NOW';
        self::$OFFSET_IN_MILLIS = 0;
        return self::now();
    }
}
