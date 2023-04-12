<?php

namespace Jabe\Impl\Util;

class ClockUtil
{
    private static $CURRENT_TIME = 'NOW';

    private static $OFFSET_IN_MILLIS = 0;

    //for testing purposes when current time is reset in parallel processes
    private static $IS_CLOCK_RESET;
    private static $CURRENT_TIMESTAMP;

    public static function setCurrentTime($currentTime, ...$args): void
    {
        if ($currentTime instanceof \DateTime) {
            $currentTime = $currentTime->format('c');
        }
        self::$CURRENT_TIME = $currentTime;

        if (!empty($args)) {
            self::$IS_CLOCK_RESET = $args[3];
            self::$CURRENT_TIMESTAMP = $args[4];

            self::$IS_CLOCK_RESET->set(1);
            self::$CURRENT_TIMESTAMP->set((new \DateTime($currentTime))->getTimestamp());
        }
    }

    public static function reset(): void
    {
        self::resetClock();
    }

    public static function getCurrentTime(...$args): \DateTime
    {
        return self::now(...$args);
    }

    public static function now(...$args): \DateTime
    {
        return self::getCurrentTimeWithOffset(...$args);
    }

    private static function getCurrentTimeWithOffset(...$args): \DateTime
    {
        $currentTime = self::$CURRENT_TIME;
        if (self::$IS_CLOCK_RESET !== null && self::$IS_CLOCK_RESET->get()) {
            $currentTime = (new \DateTime())->setTimestamp(self::$CURRENT_TIMESTAMP->get())->format('c');
        } elseif (!empty($args) && $args[3]->get()) {
            $currentTime = (new \DateTime())->setTimestamp($args[4]->get())->format('c');
        }

        if (self::$OFFSET_IN_MILLIS == 0) {
            return new \DateTime($currentTime);
        }

        $dt = new \DateTime($currentTime);
        $dt->modify('+ ' . self::$OFFSET_IN_MILLIS . ' milliseconds');
        return $dt;
    }

    /**
     * Moves the clock by the given offset and keeps it running from that point
     * on.
     *
     * @param offsetInMillis
     *          the offset to move the clock by
     * @return \DateTime the new 'now'
     */
    public static function offset(int $offsetInMillis): \DateTime
    {
        self::$OFFSET_IN_MILLIS = $offsetInMillis;
        return self::now();
    }

    public static function resetClock(...$args): \DateTime
    {
        self::$CURRENT_TIME = 'NOW';
        self::$OFFSET_IN_MILLIS = 0;
        if (self::$IS_CLOCK_RESET !== null) {
            self::$IS_CLOCK_RESET->set(0);
        }
        if (!empty($args)) {
            $args[3]->set(0);
        }
        return self::now();
    }
}
