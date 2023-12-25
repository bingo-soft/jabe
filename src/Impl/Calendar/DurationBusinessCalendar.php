<?php

namespace Jabe\Impl\Calendar;

class DurationBusinessCalendar implements BusinessCalendarInterface
{
    public const NAME = "duration";

    public function resolveDuedate(?string $duedateDescription, $startDate = null, int $repeatOffset = 0): ?\DateTime
    {
        try {
            $dh = new DurationHelper($duedateDescription, $startDate);
            return $dh->getDateAfter($startDate);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
