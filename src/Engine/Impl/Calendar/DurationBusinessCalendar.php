<?php

namespace BpmPlatform\Engine\Impl\Calendar;

use BpmPlatform\Engine\Task\TaskInterface;

class DurationBusinessCalendar implements BusinessCalendarInterface
{
    public const NAME = "duration";

    public function resolveDuedate(string $duedateDescription, $startDate = null, ?TaskInterface $task = null, ?int $repeatOffset = 0): ?\DateTime
    {
        try {
            $dh = new DurationHelper($duedateDescription, $startDate);
            return $dh->getDateAfter($startDate);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
