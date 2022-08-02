<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\Impl\Util\ClockUtil;

class DateTimeFunctions
{
    public const NOW = "now";
    public const DATE_TIME = "dateTime";

    public static function now(): \DateTime
    {
        return ClockUtil::getCurrentTime();
    }

    public static function dateTime(): \DateTime
    {
        return self::now();
    }
}
