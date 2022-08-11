<?php

namespace Jabe\Engine\Impl\Calendar;

use Jabe\Engine\Impl\Util\ClockUtil;

class DueDateBusinessCalendar implements BusinessCalendarInterface
{
    public const NAME = "dueDate";

    public function resolveDuedate(string $duedateDescription, $startDate = null, int $repeatOffset = 0): ?\DateTime
    {
        try {
            if ($startDate === null) {
                $start = ClockUtil::getCurrentTime();
            } else {
                if (is_string($startDate)) {
                    $start = new \DateTime($startDate);
                } else {
                    $start = $startDate;
                }
            }
            if (strpos($duedateDescription, "P") === 0) {
                $period = new \DateInterval($duedateDescription);
                return $start->add($period);
            } else {
                return new \DateTime($duedateDescription);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
