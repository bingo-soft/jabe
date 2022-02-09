<?php

namespace BpmPlatform\Engine\Impl\Calendar;

use BpmPlatform\Engine\Impl\Util\ClockUtil;
use BpmPlatform\Engine\Task\TaskInterface;

class DefaultBusinessCalendar implements BusinessCalendarInterface
{
    public function resolveDuedate(string $duedateDescription, $startDate = null, ?TaskInterface $task = null, ?int $repeatOffset = 0): ?\DateTime
    {
        if (is_string($startDate)) {
            $startDate = new \DateTime($startDate);
        }
        $resolvedDuedate = $startDate == null ? ClockUtil::getCurrentTime() : $startDate;
        $period = \DateInterval::createFromDateString($duedateDescription);
        return $resolvedDuedate->add($period);
    }
}
