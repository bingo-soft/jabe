<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Calendar\{
    CycleBusinessCalendar,
    DueDateBusinessCalendar,
    DurationBusinessCalendar
};

class TimerDeclarationType
{
    public const DATE = DueDateBusinessCalendar::NAME;
    public const DURATION = DurationBusinessCalendar::NAME;
    public const CYCLE = CycleBusinessCalendar::NAME;

    public static function calendarName(string $type): ?string
    {
        switch (strtolower($type)) {
            case 'date':
                return self::DATE;
            case 'duration':
                return self::DURATION;
            case 'cycle':
                return self::CYCLE;
            default:
                return null;
        }
    }
}
