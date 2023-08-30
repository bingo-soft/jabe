<?php

namespace Jabe\Impl\Calendar;

use Jabe\Impl\Util\ClockUtil;

class DefaultBusinessCalendar implements BusinessCalendarInterface
{
    public function resolveDuedate(?string $duedateDescription, $startDate = null, int $repeatOffset = 0): ?\DateTime
    {
        if (is_string($startDate)) {
            $startDate = new \DateTime($startDate);
        }
        $resolvedDuedate = $startDate === null ? ClockUtil::getCurrentTime() : $startDate;
        $period = \DateInterval::createFromDateString($duedateDescription);
        return $resolvedDuedate->add($period);
    }
}
