<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Calendar\{
    CycleBusinessCalendar,
    DueDateBusinessCalendar,
    DurationBusinessCalendar
};

class TimerDeclarationType
{
    public const DATE = DueDateBusinessCalendar::NAME;
    public const DURATION = DurationBusinessCalendar::NAME;
    public const CYCLE = CycleBusinessCalendar::NAME;

    public static function calendarName(?string $type): ?string
    {
        switch ($type) {
            case self::DATE:
                return self::DATE;
            case self::DURATION:
                return self::DURATION;
            case self::CYCLE:
                return self::CYCLE;
            default:
                return null;
        }
    }
}
