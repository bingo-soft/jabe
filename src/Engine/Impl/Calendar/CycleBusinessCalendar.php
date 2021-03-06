<?php

namespace Jabe\Engine\Impl\Calendar;

use Cron\CronExpression;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EngineUtilLogger
};
use Jabe\Engine\Task\TaskInterface;

class CycleBusinessCalendar implements BusinessCalendarInterface
{
    //private final static EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;

    public const NAME = "cycle";

    public function resolveDuedate(string $duedateDescription, $startDate = null, ?TaskInterface $task = null, ?int $repeatOffset = 0): ?\DateTime
    {
        try {
            if (strpos($duedateDescription, "R") === 0) {
                $durationHelper = new DurationHelper($duedateDescription, $startDate);
                $durationHelper->setRepeatOffset($repeatOffset);
                return $durationHelper->getDateAfter($startDate);
            } else {
                $cron = new CronExpression($duedateDescription);
                return $cron->getNextRunDate($startDate ?? ClockUtil::getCurrentTime());
            }
        } catch (\Exception $e) {
            //throw LOG.exceptionWhileParsingCronExpresison(duedateDescription, e);
            throw $e;
        }
    }
}
